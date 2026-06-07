<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Attribute\Repositories\AttributeOptionRepository;
use Webkul\Attribute\Repositories\AttributeFamilyRepository;
use Webkul\Product\Repositories\ProductRepository;
use Webkul\Product\Models\Product;
use Illuminate\Support\Facades\DB;

class PopulateProductData extends Command
{
    protected $signature = 'bagisto:populate-data';
    protected $description = 'Populate product attributes, options, and relations';

    public function handle(
        AttributeRepository $attributeRepository,
        AttributeOptionRepository $attributeOptionRepository,
        AttributeFamilyRepository $attributeFamilyRepository,
        ProductRepository $productRepository
    ) {
        $this->info('Starting population...');

        // 1. Create Attributes if they don't exist
        $attributesConfig = [
            'size' => ['S', 'M', 'L', 'XL'],
            'color' => ['Red', 'Blue', 'Green', 'Black', 'White'],
            'brand' => ['Nike', 'Adidas', 'Puma', 'Gucci', 'Prada'],
            'age' => ['Kids', 'Teens', 'Adults'],
        ];

        $createdAttributes = [];

        foreach ($attributesConfig as $code => $options) {
            $attribute = $attributeRepository->findOneByField('code', $code);

            if (! $attribute) {
                $attribute = $attributeRepository->create([
                    'code' => $code,
                    'admin_name' => ucfirst($code),
                    'type' => 'select',
                    'validation' => '',
                    'position' => 1,
                    'is_required' => 0,
                    'is_unique' => 0,
                    'value_per_locale' => 0,
                    'value_per_channel' => 0,
                    'is_filterable' => 1,
                    'is_configurable' => 1,
                    'is_user_defined' => 1,
                    'is_visible_on_front' => 1,
                    'is_comparable' => 1,
                    'en' => ['name' => ucfirst($code)]
                ]);
                $this->info("Created attribute: $code");
            } else {
                $attributeRepository->update([
                    'type' => $attribute->type,
                    'is_filterable' => 1,
                    'is_configurable' => 1,
                    'is_visible_on_front' => 1,
                ], $attribute->id);
            }

            // Create options
            $optionIds = [];
            foreach ($options as $index => $optionName) {
                $existingOption = $attribute->options()->where('admin_name', $optionName)->first();
                if (! $existingOption) {
                    $existingOption = $attributeOptionRepository->create([
                        'admin_name' => $optionName,
                        'sort_order' => $index,
                        'attribute_id' => $attribute->id,
                        'en' => ['label' => $optionName]
                    ]);
                }
                $optionIds[] = $existingOption->id;
            }

            $createdAttributes[$code] = [
                'attribute' => $attribute,
                'optionIds' => $optionIds
            ];
        }

        // 2. Assign to default Attribute Family
        $family = $attributeFamilyRepository->find(1); 
        if ($family) {
            $groupId = DB::table('attribute_groups')->where('attribute_family_id', $family->id)->where('code', 'general')->value('id');
            if (!$groupId) {
                $groupId = DB::table('attribute_groups')->where('attribute_family_id', $family->id)->value('id');
            }

            foreach ($createdAttributes as $data) {
                $exists = DB::table('attribute_group_mappings')
                    ->where('attribute_id', $data['attribute']->id)
                    ->where('attribute_group_id', $groupId)
                    ->exists();

                if (! $exists && $groupId) {
                    $maxPos = DB::table('attribute_group_mappings')->where('attribute_group_id', $groupId)->max('position');
                    DB::table('attribute_group_mappings')->insert([
                        'attribute_id' => $data['attribute']->id,
                        'attribute_group_id' => $groupId,
                        'position' => $maxPos + 1
                    ]);
                }
            }
        }

        // 3. Assign random values to all products & set relations
        $products = Product::all();
        foreach ($products as $product) {
            $updateData = [];

            foreach ($createdAttributes as $code => $data) {
                $updateData[$code] = $data['optionIds'][array_rand($data['optionIds'])];
            }

            // We must use DB raw to insert into product_attribute_values to skip validation if needed,
            // or just rely on ProductRepository.
            // Using DB is safer for mass update to avoid events wiping out other data.
            foreach ($updateData as $code => $optionId) {
                $attribute = $createdAttributes[$code]['attribute'];
                
                // Check if exists
                $valExists = DB::table('product_attribute_values')
                    ->where('product_id', $product->id)
                    ->where('attribute_id', $attribute->id)
                    ->first();
                    
                if ($valExists) {
                    DB::table('product_attribute_values')
                        ->where('id', $valExists->id)
                        ->update(['integer_value' => $optionId]);
                } else {
                    DB::table('product_attribute_values')->insert([
                        'product_id' => $product->id,
                        'attribute_id' => $attribute->id,
                        'channel' => null,
                        'locale' => null,
                        'integer_value' => $optionId,
                    ]);
                }
            }

            // Relations
            $categoryIds = $product->categories->pluck('id')->toArray();
            
            if (!empty($categoryIds)) {
                $baseQuery = Product::whereHas('categories', function($q) use ($categoryIds) {
                    $q->whereIn('category_id', $categoryIds);
                })->where('id', '!=', $product->id);
            } else {
                $baseQuery = Product::where('id', '!=', $product->id);
            }

            $relatedQuery = (clone $baseQuery)->inRandomOrder()->limit(4)->pluck('id')->toArray();
            $upSells = (clone $baseQuery)->inRandomOrder()->limit(4)->pluck('id')->toArray();
            $crossSells = (clone $baseQuery)->inRandomOrder()->limit(4)->pluck('id')->toArray();

            $product->related_products()->sync($relatedQuery);
            $product->up_sells()->sync($upSells);
            $product->cross_sells()->sync($crossSells);

            $this->info("Updated product ID: {$product->id}");
        }

        // 4. Reindex Elasticsearch
        $this->call('indexer:index');

        $this->info('Successfully populated product data!');
    }
}
