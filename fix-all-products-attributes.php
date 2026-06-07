<?php
/**
 * Fix ALL product attribute values by processing each attribute type separately
 * This avoids SQL column count mismatch errors
 */

require '/var/www/bagisto/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

$app = require_once '/var/www/bagisto/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "========================================\n";
echo "Fix ALL Product Attribute Values\n";
echo "========================================\n\n";

// Get attribute IDs
$attributes = DB::table('attributes')
    ->whereIn('code', ['name', 'price', 'description', 'short_description', 'url_key', 'status', 'weight'])
    ->get()
    ->keyBy('code');

echo "Found " . count($attributes) . " attributes\n\n";

// Get all products from product_flat that have data
$flatProducts = DB::table('product_flat')
    ->select('product_id', 'name', 'price', 'description', 'short_description', 'url_key', 'status')
    ->where('channel', 'default')
    ->where('locale', 'en')
    ->whereNotNull('name')
    ->where('name', '!=', '')
    ->get();

echo "Found " . count($flatProducts) . " products with data\n\n";

$processed = 0;
$skipped = 0;
$batchSize = 500;

// Process TEXT attributes (name, description, short_description, url_key)
echo "Processing TEXT attributes...\n";
$textBatch = [];
foreach ($flatProducts as $i => $product) {
    if (($i + 1) % 500 == 0) {
        echo "  Progress: " . ($i + 1) . "/" . count($flatProducts) . "\n";
    }
    
    // Check if already has attributes
    $exists = DB::table('product_attribute_values')
        ->where('product_id', $product->product_id)
        ->exists();
    
    if ($exists) {
        $skipped++;
        continue;
    }
    
    // Name
    if ($product->name && isset($attributes['name'])) {
        $textBatch[] = [
            'product_id' => $product->product_id,
            'attribute_id' => $attributes['name']->id,
            'locale' => 'en',
            'channel' => 'default',
            'text_value' => $product->name,
            'unique_id' => "default|en|{$product->product_id}|{$attributes['name']->id}",
        ];
    }
    
    // Description
    if ($product->description && isset($attributes['description'])) {
        $textBatch[] = [
            'product_id' => $product->product_id,
            'attribute_id' => $attributes['description']->id,
            'locale' => 'en',
            'channel' => 'default',
            'text_value' => $product->description,
            'unique_id' => "default|en|{$product->product_id}|{$attributes['description']->id}",
        ];
    }
    
    // Short description
    if ($product->short_description && isset($attributes['short_description'])) {
        $textBatch[] = [
            'product_id' => $product->product_id,
            'attribute_id' => $attributes['short_description']->id,
            'locale' => 'en',
            'channel' => 'default',
            'text_value' => $product->short_description,
            'unique_id' => "default|en|{$product->product_id}|{$attributes['short_description']->id}",
        ];
    }
    
    // URL Key
    if ($product->url_key && isset($attributes['url_key'])) {
        $textBatch[] = [
            'product_id' => $product->product_id,
            'attribute_id' => $attributes['url_key']->id,
            'locale' => 'en',
            'channel' => 'default',
            'text_value' => $product->url_key,
            'unique_id' => "default|en|{$product->product_id}|{$attributes['url_key']->id}",
        ];
    }
    
    // Insert batch when it reaches batch size
    if (count($textBatch) >= $batchSize) {
        DB::table('product_attribute_values')->insert($textBatch);
        $textBatch = [];
    }
}

// Insert remaining text batch
if (!empty($textBatch)) {
    DB::table('product_attribute_values')->insert($textBatch);
}

echo "✅ TEXT attributes done\n\n";

// Process FLOAT attributes (price, weight)
echo "Processing FLOAT attributes...\n";
$floatBatch = [];
foreach ($flatProducts as $i => $product) {
    if (($i + 1) % 500 == 0) {
        echo "  Progress: " . ($i + 1) . "/" . count($flatProducts) . "\n";
    }
    
    // Check if already has attributes
    $exists = DB::table('product_attribute_values')
        ->where('product_id', $product->product_id)
        ->exists();
    
    if ($exists) {
        continue;
    }
    
    // Price
    if ($product->price && isset($attributes['price'])) {
        $floatBatch[] = [
            'product_id' => $product->product_id,
            'attribute_id' => $attributes['price']->id,
            'channel' => 'default',
            'float_value' => (float) $product->price,
            'unique_id' => "default||{$product->product_id}|{$attributes['price']->id}",
        ];
    }
    
    // Weight (default value)
    if (isset($attributes['weight'])) {
        $floatBatch[] = [
            'product_id' => $product->product_id,
            'attribute_id' => $attributes['weight']->id,
            'float_value' => 0.5,
            'unique_id' => "||{$product->product_id}|{$attributes['weight']->id}",
        ];
    }
    
    // Insert batch when it reaches batch size
    if (count($floatBatch) >= $batchSize) {
        DB::table('product_attribute_values')->insert($floatBatch);
        $floatBatch = [];
    }
}

// Insert remaining float batch
if (!empty($floatBatch)) {
    DB::table('product_attribute_values')->insert($floatBatch);
}

echo "✅ FLOAT attributes done\n\n";

// Process BOOLEAN attributes (status)
echo "Processing BOOLEAN attributes...\n";
$boolBatch = [];
foreach ($flatProducts as $i => $product) {
    if (($i + 1) % 500 == 0) {
        echo "  Progress: " . ($i + 1) . "/" . count($flatProducts) . "\n";
        $processed = $i + 1;
    }
    
    // Check if already has attributes
    $exists = DB::table('product_attribute_values')
        ->where('product_id', $product->product_id)
        ->exists();
    
    if ($exists) {
        continue;
    }
    
    // Status
    if (isset($product->status) && isset($attributes['status'])) {
        $boolBatch[] = [
            'product_id' => $product->product_id,
            'attribute_id' => $attributes['status']->id,
            'channel' => 'default',
            'boolean_value' => $product->status ? 1 : 0,
            'unique_id' => "default||{$product->product_id}|{$attributes['status']->id}",
        ];
    }
    
    // Insert batch when it reaches batch size
    if (count($boolBatch) >= $batchSize) {
        DB::table('product_attribute_values')->insert($boolBatch);
        $boolBatch = [];
    }
}

// Insert remaining bool batch
if (!empty($boolBatch)) {
    DB::table('product_attribute_values')->insert($boolBatch);
}

echo "✅ BOOLEAN attributes done\n\n";

echo "========================================\n";
echo "Summary:\n";
echo "  Total products: " . count($flatProducts) . "\n";
echo "  Processed: $processed\n";
echo "  Skipped (already had attributes): $skipped\n";
echo "========================================\n\n";

// Verify results
echo "Verification:\n";
$totalAttributeValues = DB::table('product_attribute_values')->count();
$productsWithAttributes = DB::table('product_attribute_values')
    ->distinct('product_id')
    ->count('product_id');

echo "  Total attribute values: $totalAttributeValues\n";
echo "  Products with attributes: $productsWithAttributes\n";

// Check some sample products
$samples = [1, 100, 1000, 8479];
echo "\n  Sample products:\n";
foreach ($samples as $pid) {
    $count = DB::table('product_attribute_values')->where('product_id', $pid)->count();
    echo "    Product $pid: $count attributes\n";
}

echo "\n✅ Done! Admin panel should now work for all products.\n";
