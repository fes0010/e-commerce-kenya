<?php
/**
 * Populate product_attribute_values from product_flat
 * The admin panel needs product_attribute_values to edit products
 */

require '/var/www/bagisto/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

$app = require_once '/var/www/bagisto/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "========================================\n";
echo "Populate Product Attribute Values\n";
echo "========================================\n\n";

// Get attribute IDs
$attributes = [
    'name' => DB::table('attributes')->where('code', 'name')->value('id'),
    'price' => DB::table('attributes')->where('code', 'price')->value('id'),
    'description' => DB::table('attributes')->where('code', 'description')->value('id'),
    'short_description' => DB::table('attributes')->where('code', 'short_description')->value('id'),
    'url_key' => DB::table('attributes')->where('code', 'url_key')->value('id'),
    'status' => DB::table('attributes')->where('code', 'status')->value('id'),
];

echo "Attribute IDs:\n";
foreach ($attributes as $code => $id) {
    echo "  $code: $id\n";
}
echo "\n";

// Get all products from product_flat
echo "Loading products from product_flat...\n";
$products = DB::table('product_flat')->get();
echo "Found " . count($products) . " products\n\n";

echo "Processing products...\n";
$processed = 0;
$skipped = 0;
$errors = 0;
$batchSize = 100;
$batch = [];

foreach ($products as $i => $product) {
    if (($i + 1) % 100 == 0) {
        echo "  Progress: " . ($i + 1) . "/" . count($products) . " ($processed processed, $skipped skipped, $errors errors)\n";
    }
    
    try {
        // Check if already has attribute values
        $hasAttributes = DB::table('product_attribute_values')
            ->where('product_id', $product->product_id)
            ->exists();
        
        if ($hasAttributes) {
            $skipped++;
            continue;
        }
        
        // Create attribute values array
        $attributeValues = [];
        
        // Name
        if (!empty($product->name)) {
            $batch[] = [
                'product_id' => $product->product_id,
                'attribute_id' => $attributes['name'],
                'locale' => $product->locale,
                'channel' => $product->channel,
                'text_value' => $product->name,
                'unique_id' => "{$product->channel}|{$product->locale}|{$product->product_id}|{$attributes['name']}",
            ];
        }
        
        // Price
        if (!empty($product->price)) {
            $batch[] = [
                'product_id' => $product->product_id,
                'attribute_id' => $attributes['price'],
                'locale' => null,
                'channel' => $product->channel,
                'float_value' => (float) $product->price,
                'unique_id' => "{$product->channel}||{$product->product_id}|{$attributes['price']}",
            ];
        }
        
        // Description
        if (!empty($product->description)) {
            $batch[] = [
                'product_id' => $product->product_id,
                'attribute_id' => $attributes['description'],
                'locale' => $product->locale,
                'channel' => $product->channel,
                'text_value' => $product->description,
                'unique_id' => "{$product->channel}|{$product->locale}|{$product->product_id}|{$attributes['description']}",
            ];
        }
        
        // Short description
        if (!empty($product->short_description)) {
            $batch[] = [
                'product_id' => $product->product_id,
                'attribute_id' => $attributes['short_description'],
                'locale' => $product->locale,
                'channel' => $product->channel,
                'text_value' => $product->short_description,
                'unique_id' => "{$product->channel}|{$product->locale}|{$product->product_id}|{$attributes['short_description']}",
            ];
        }
        
        // URL Key
        if (!empty($product->url_key)) {
            $batch[] = [
                'product_id' => $product->product_id,
                'attribute_id' => $attributes['url_key'],
                'locale' => $product->locale,
                'channel' => $product->channel,
                'text_value' => $product->url_key,
                'unique_id' => "{$product->channel}|{$product->locale}|{$product->product_id}|{$attributes['url_key']}",
            ];
        }
        
        // Status
        $batch[] = [
            'product_id' => $product->product_id,
            'attribute_id' => $attributes['status'],
            'locale' => null,
            'channel' => $product->channel,
            'boolean_value' => $product->status ?? 1,
            'unique_id' => "{$product->channel}||{$product->product_id}|{$attributes['status']}",
        ];
        
        $processed++;
        
        // Insert batch when it reaches batch size
        if (count($batch) >= $batchSize * 6) { // 6 attributes per product
            DB::table('product_attribute_values')->insert($batch);
            $batch = [];
        }
        
    } catch (\Exception $e) {
        echo "\nError processing product ID {$product->product_id}: " . $e->getMessage() . "\n";
        $errors++;
    }
}

// Insert remaining batch
if (!empty($batch)) {
    DB::table('product_attribute_values')->insert($batch);
}

echo "\n========================================\n";
echo "Summary:\n";
echo "  Total products: " . count($products) . "\n";
echo "  Processed: $processed\n";
echo "  Skipped: $skipped\n";
echo "  Errors: $errors\n";
echo "========================================\n\n";

// Verify results
echo "Verification:\n";
$totalAttributeValues = DB::table('product_attribute_values')->count();
$productsWithAttributes = DB::table('product_attribute_values')
    ->distinct('product_id')
    ->count('product_id');

echo "  Total attribute values: $totalAttributeValues\n";
echo "  Products with attributes: $productsWithAttributes\n";
echo "\nDone!\n";
