<?php
/**
 * Sync product_flat data to product_attribute_values
 * The admin panel requires EAV attributes to be populated
 */

require '/var/www/bagisto/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

$app = require_once '/var/www/bagisto/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "========================================\n";
echo "Sync Flat to Attribute Values\n";
echo "========================================\n\n";

// Get configuration
$channelCode = 'default';
$localeCode = 'en';

// Get attribute IDs
$attributes = DB::table('attributes')
    ->whereIn('code', ['name', 'price', 'description', 'short_description', 'url_key', 'status', 'weight'])
    ->get()
    ->keyBy('code');

echo "Found " . count($attributes) . " attributes\n\n";

// Get all products from product_flat
echo "Loading products from product_flat...\n";
$flatProducts = DB::table('product_flat')
    ->where('channel', $channelCode)
    ->where('locale', $localeCode)
    ->whereNotNull('name')
    ->where('name', '!=', '')
    ->get();

echo "Found " . count($flatProducts) . " products with data\n\n";

echo "Processing products...\n";
$processed = 0;
$skipped = 0;
$errors = 0;
$batchSize = 100;
$batch = [];

foreach ($flatProducts as $i => $flat) {
    if (($i + 1) % 100 == 0) {
        echo "  Progress: " . ($i + 1) . "/" . count($flatProducts) . " ($processed processed, $skipped skipped, $errors errors)\n";
    }
    
    try {
        $productId = $flat->product_id;
        
        // Check if already has attribute values
        $hasAttributes = DB::table('product_attribute_values')
            ->where('product_id', $productId)
            ->exists();
        
        if ($hasAttributes) {
            $skipped++;
            continue;
        }
        
        // Create attribute values array
        $attributeValues = [];
        
        // Name (locale-specific, text)
        if (!empty($flat->name) && isset($attributes['name'])) {
            $attributeValues[] = [
                'product_id' => $productId,
                'attribute_id' => $attributes['name']->id,
                'locale' => $localeCode,
                'channel' => $channelCode,
                'text_value' => $flat->name,
                'unique_id' => "{$channelCode}|{$localeCode}|{$productId}|{$attributes['name']->id}",
            ];
        }
        
        // Price (channel-specific, float)
        if (!empty($flat->price) && isset($attributes['price'])) {
            $attributeValues[] = [
                'product_id' => $productId,
                'attribute_id' => $attributes['price']->id,
                'locale' => null,
                'channel' => $channelCode,
                'float_value' => (float) $flat->price,
                'unique_id' => "{$channelCode}||{$productId}|{$attributes['price']->id}",
            ];
        }
        
        // Description (locale-specific, text)
        if (!empty($flat->description) && isset($attributes['description'])) {
            $attributeValues[] = [
                'product_id' => $productId,
                'attribute_id' => $attributes['description']->id,
                'locale' => $localeCode,
                'channel' => $channelCode,
                'text_value' => $flat->description,
                'unique_id' => "{$channelCode}|{$localeCode}|{$productId}|{$attributes['description']->id}",
            ];
        }
        
        // Short description (locale-specific, text)
        if (!empty($flat->short_description) && isset($attributes['short_description'])) {
            $attributeValues[] = [
                'product_id' => $productId,
                'attribute_id' => $attributes['short_description']->id,
                'locale' => $localeCode,
                'channel' => $channelCode,
                'text_value' => $flat->short_description,
                'unique_id' => "{$channelCode}|{$localeCode}|{$productId}|{$attributes['short_description']->id}",
            ];
        }
        
        // URL Key (locale-specific, text)
        if (!empty($flat->url_key) && isset($attributes['url_key'])) {
            $attributeValues[] = [
                'product_id' => $productId,
                'attribute_id' => $attributes['url_key']->id,
                'locale' => $localeCode,
                'channel' => $channelCode,
                'text_value' => $flat->url_key,
                'unique_id' => "{$channelCode}|{$localeCode}|{$productId}|{$attributes['url_key']->id}",
            ];
        }
        
        // Status (channel-specific, boolean)
        if (isset($flat->status) && isset($attributes['status'])) {
            $attributeValues[] = [
                'product_id' => $productId,
                'attribute_id' => $attributes['status']->id,
                'locale' => null,
                'channel' => $channelCode,
                'boolean_value' => $flat->status ? 1 : 0,
                'unique_id' => "{$channelCode}||{$productId}|{$attributes['status']->id}",
            ];
        }
        
        // Weight (global, float) - default value
        if (isset($attributes['weight'])) {
            $attributeValues[] = [
                'product_id' => $productId,
                'attribute_id' => $attributes['weight']->id,
                'locale' => null,
                'channel' => null,
                'float_value' => 0.5,
                'unique_id' => "||{$productId}|{$attributes['weight']->id}",
            ];
        }
        
        // Add to batch
        if (!empty($attributeValues)) {
            $batch = array_merge($batch, $attributeValues);
            $processed++;
        }
        
        // Insert batch when it reaches batch size
        if (count($batch) >= $batchSize * 7) { // 7 attributes per product approx
            DB::table('product_attribute_values')->insert($batch);
            $batch = [];
        }
        
    } catch (\Exception $e) {
        echo "\nError processing product {$flat->product_id}: " . $e->getMessage() . "\n";
        $errors++;
    }
}

// Insert remaining batch
if (!empty($batch)) {
    DB::table('product_attribute_values')->insert($batch);
}

echo "\n========================================\n";
echo "Summary:\n";
echo "  Total products: " . count($flatProducts) . "\n";
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

// Check product 8479 specifically
$attrs8479 = DB::table('product_attribute_values')->where('product_id', 8479)->count();
echo "  Attribute values for product 8479: $attrs8479\n";

echo "\nDone! Admin panel should now show product data.\n";
