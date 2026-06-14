<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Category\Models\Category;
use Webkul\Category\Repositories\CategoryRepository;
use Webkul\Product\Repositories\ProductRepository;

class ImportNilaProducts extends Command
{
    protected $signature = 'import:nila-products {file=scrapers/nila_products.json}';

    protected $description = 'Import products from Nila Baby Shop JSON';

    private $productRepository;

    private $categoryRepository;

    private $attributeRepository;

    private $categoryMap = [];

    public function __construct(
        ProductRepository $productRepository,
        CategoryRepository $categoryRepository,
        AttributeRepository $attributeRepository
    ) {
        parent::__construct();
        $this->productRepository = $productRepository;
        $this->categoryRepository = $categoryRepository;
        $this->attributeRepository = $attributeRepository;
    }

    public function handle()
    {
        $file = base_path($this->argument('file'));

        if (! file_exists($file)) {
            $this->error("File not found: {$file}");

            return 1;
        }

        $products = json_decode(file_get_contents($file), true);
        $this->info('Found '.count($products).' products to import');

        $bar = $this->output->createProgressBar(count($products));
        $bar->start();

        $imported = 0;
        $skipped = 0;

        foreach ($products as $productData) {
            try {
                if ($this->importProduct($productData)) {
                    $imported++;
                } else {
                    $skipped++;
                }
            } catch (\Exception $e) {
                $this->error("\nError importing {$productData['name']}: ".$e->getMessage());
                $skipped++;
            }
            $bar->advance();
        }

        $bar->finish();

        $this->info("\nFixing category tree...");
        Category::fixTree();

        $this->newLine(2);
        $this->info('Import complete!');
        $this->info("Imported: {$imported}");
        $this->info("Skipped: {$skipped}");

        return 0;
    }

    private function importProduct($data)
    {
        $sku = $this->generateSku($data['name']);

        // Check if product already exists
        $existing = DB::table('product_flat')->where('name', $data['name'])->first();

        $price = 0;
        if (isset($data['price']) && $data['price']) {
            $price = $this->extractPrice($data['price']);
        }

        if ($existing) {
            if ($price > 0) {
                DB::table('product_flat')->where('product_id', $existing->product_id)->update(['price' => $price]);
            }
            // Still process categories for image/position
            $categoryIds = $this->getCategoryIds($data['categories']);
            if (! empty($data['images'])) {
                $this->attachCategoryImagesOnly($existing->product_id, $data['images'], $categoryIds);
            }

            return false; // Skip full import
        }

        // Add price if available
        $price = 0;
        if (isset($data['price']) && $data['price']) {
            $price = $this->extractPrice($data['price']);
        }

        $categoryIds = $this->getCategoryIds($data['categories']);

        // Step 1: Create basic product
        $product = $this->productRepository->create([
            'type' => 'simple',
            'attribute_family_id' => 1,
            'sku' => $sku,
        ]);

        // Step 2: Update with full data
        $this->productRepository->update([
            'channel' => 'default',
            'locale' => 'en',
            'name' => $data['name'],
            'url_key' => Str::slug($data['name']),
            'short_description' => $this->truncate($data['description'] ?? '', 500),
            'description' => $data['description'] ?? '',
            'meta_title' => $data['name'],
            'meta_keywords' => implode(', ', $data['categories']),
            'meta_description' => $this->truncate($data['description'] ?? '', 160),
            'price' => $price,
            'cost' => null,
            'special_price' => null,
            'weight' => 1,
            'status' => 1,
            'visible_individually' => 1,
            'guest_checkout' => 1,
            'featured' => 1,
            'new' => 1,
            'manage_stock' => 0,
            'categories' => $categoryIds,
            'inventories' => [1 => 0],
        ], $product->id);

        // Download and attach images
        if (! empty($data['images'])) {
            $this->attachImages($product, $data['images'], $categoryIds);
        }

        return true;
    }

    private function getCategoryIds($categories)
    {
        if (empty($categories)) {
            return [1]; // Default root category
        }

        $ids = [];
        $parentId = 1;

        foreach ($categories as $categoryName) {
            $cacheKey = $parentId.'_'.$categoryName;

            if (isset($this->categoryMap[$cacheKey])) {
                $parentId = $this->categoryMap[$cacheKey];
                $ids[] = $parentId;

                continue;
            }

            $category = DB::table('category_translations')
                ->where('name', $categoryName)
                ->where('locale', 'en')
                ->first();

            if ($category) {
                $parentId = $category->category_id;
                // Move it to first in carousel
                DB::table('categories')->where('id', $parentId)->update(['position' => 1]);
            } else {
                // Create new category using DB inserts directly to avoid nestedset errors
                $parentId = DB::table('categories')->insertGetId([
                    'parent_id' => $parentId,
                    'position' => 1,
                    'status' => 1,
                    'display_mode' => 'products_and_description',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                DB::table('category_translations')->insert([
                    'category_id' => $parentId,
                    'name' => $categoryName,
                    'slug' => Str::slug($categoryName),
                    'locale' => 'en',
                ]);
            }

            $this->categoryMap[$cacheKey] = $parentId;
            $ids[] = $parentId;
        }

        return $ids ?: [1];
    }

    private function attachImages($product, $images, $categoryIds = [])
    {
        $firstImage = null;
        foreach ($images as $index => $imageUrl) {
            try {
                $imageContent = @file_get_contents($imageUrl);
                if (! $imageContent) {
                    continue;
                }

                $extension = pathinfo(parse_url($imageUrl, PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
                $filename = 'product/'.$product->id.'/'.uniqid().'.'.$extension;

                Storage::disk('public')->put($filename, $imageContent);

                if ($index === 0) {
                    $firstImage = $filename;
                }

                DB::table('product_images')->insert([
                    'product_id' => $product->id,
                    'type' => 'images',
                    'path' => $filename,
                    'position' => $index,
                ]);

            } catch (\Exception $e) {
                // Skip failed image
                continue;
            }
        }

        if ($firstImage && ! empty($categoryIds)) {
            foreach ($categoryIds as $catId) {
                $cat = DB::table('categories')->where('id', $catId)->first();
                if ($cat && empty($cat->logo_path)) {
                    DB::table('categories')->where('id', $catId)->update(['logo_path' => $firstImage]);
                }
            }
        }
    }

    private function attachCategoryImagesOnly($productId, $images, $categoryIds)
    {
        if (empty($images) || empty($categoryIds)) {
            return;
        }

        $extension = pathinfo(parse_url($images[0], PHP_URL_PATH), PATHINFO_EXTENSION) ?: 'jpg';
        $filename = 'product/'.$productId.'/'.uniqid().'.'.$extension;

        try {
            $imageContent = @file_get_contents($images[0]);
            if ($imageContent) {
                Storage::disk('public')->put($filename, $imageContent);
                foreach ($categoryIds as $catId) {
                    $cat = DB::table('categories')->where('id', $catId)->first();
                    if ($cat && empty($cat->logo_path)) {
                        DB::table('categories')->where('id', $catId)->update(['logo_path' => $filename]);
                    }
                }
            }
        } catch (\Exception $e) {
        }
    }

    private function generateSku($name)
    {
        return 'NILA-'.strtoupper(Str::substr(preg_replace('/[^A-Za-z0-9]/', '', $name), 0, 20)).'-'.rand(1000, 9999);
    }

    private function extractPrice($priceString)
    {
        preg_match('/[\d,]+\.?\d*/', $priceString, $matches);

        return isset($matches[0]) ? (float) str_replace(',', '', $matches[0]) : 0;
    }

    private function truncate($text, $length)
    {
        return Str::limit(strip_tags($text), $length);
    }
}
