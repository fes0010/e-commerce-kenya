<?php
/**
 * Fix missing product data in product_flat table
 * This populates the product_flat table with data from the source JSON
 */

require '/var/www/bagisto/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

$app = require_once '/var/www/bagisto/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "========================================\n";
echo "Fix Product Flat Data\n";
echo "========================================\n\n";

// Load source data
echo "Loading product data from JSON...\n";
$products = json_decode(file_get_contents('/tmp/peekaboo_products.json'), true);
echo "Found " . count($products) . " products in source file\n\n";

// Get channel and locale
$channelId = 1;
$localeCode = 'en';

// Get all products from database
echo "Loading products from database...\n";
$dbProducts = DB::table('products')->get()->keyBy('sku');
echo "Found " . count($dbProducts) . " products in database\n\n";

// Find products without names
$productsWithoutNames = DB::table('product_flat')
    ->where(function($q) {
        $q->whereNull('name')->orWhere('name', '');
    })
    ->pluck('product_id')
    ->toArray();

echo "Found " . count($productsWithoutNames) . " products without names in product_flat\n\n";

echo "Processing products...\n";
$processed = 0;
$skipped = 0;
$errors = 0;

foreach ($products as $i => $sourceProduct) {
    if (($i + 1) % 100 == 0) {
        echo "  Progress: " . ($i + 1) . "/" . count($products) . " ($processed processed, $skipped skipped, $errors errors)\n";
    }
    
    try {
        // Find matching product in database
        $sku = $sourceProduct['sku'] ?: 'PB-' . $sourceProduct['shopify_id'];
        
        if (!isset($dbProducts[$sku])) {
            $skipped++;
            continue;
        }
        
        $productId = $dbProducts[$sku]->id;
        
        // Check if this product needs fixing
        if (!in_array($productId, $productsWithoutNames)) {
            $skipped++;
            continue;
        }
        
        // Prepare data
        $urlKey = $sourceProduct['handle'] ?: Str::slug($sourceProduct['name'] ?: $sku);
        $timestamp = now();
        
        // Update product_flat
        $updated = DB::table('product_flat')
            ->where('product_id', $productId)
            ->where('channel', 'default')
            ->where('locale', $localeCode)
            ->update([
                'name' => $sourceProduct['name'] ?? null,
                'short_description' => $sourceProduct['short_description'] ?? null,
                'description' => $sourceProduct['long_description'] ?? null,
                'price' => $sourceProduct['price'] ?? null,
                'url_key' => $urlKey,
                'status' => $sourceProduct['available'] ? 1 : 0,
                'visible_individually' => 1,
                'updated_at' => $timestamp,
            ]);
        
        if ($updated) {
            $processed++;
        } else {
            // Try insert if update failed (record might not exist)
            $product = $dbProducts[$sku];
            DB::table('product_flat')->insert([
                'product_id' => $productId,
                'sku' => $sku,
                'type' => $product->type,
                'name' => $sourceProduct['name'] ?? null,
                'short_description' => $sourceProduct['short_description'] ?? null,
                'description' => $sourceProduct['long_description'] ?? null,
                'price' => $sourceProduct['price'] ?? null,
                'url_key' => $urlKey,
                'status' => $sourceProduct['available'] ? 1 : 0,
                'visible_individually' => 1,
                'channel' => 'default',
                'locale' => $localeCode,
                'created_at' => $timestamp,
                'updated_at' => $timestamp,
            ]);
            $processed++;
        }
        
    } catch (\Exception $e) {
        echo "\nError processing product SKU $sku: " . $e->getMessage() . "\n";
        $errors++;
    }
}

echo "\n========================================\n";
echo "Summary:\n";
echo "  Total products in source: " . count($products) . "\n";
echo "  Processed: $processed\n";
echo "  Skipped: $skipped\n";
echo "  Errors: $errors\n";
echo "========================================\n\n";

// Verify results
echo "Verification:\n";
$productsWithNames = DB::table('product_flat')
    ->whereNotNull('name')
    ->where('name', '!=', '')
    ->count();
$productsWithoutNames = DB::table('product_flat')
    ->where(function($q) {
        $q->whereNull('name')->orWhere('name', '');
    })
    ->count();

echo "  Products with names: $productsWithNames\n";
echo "  Products without names: $productsWithoutNames\n";
echo "\nDone!\n";
