<?php
/**
 * Fix missing product_attribute_values table
 * The admin panel requires product_attribute_values (EAV system)
 * while the storefront uses product_flat
 */

require '/var/www/bagisto/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

$app = require_once '/var/www/bagisto/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "========================================\n";
echo "Fix Product Attribute Values (EAV)\n";
echo "========================================\n\n";

// Load source data
echo "Loading product data from JSON...\n";
$products = json_decode(file_get_contents('/tmp/peekaboo_products.json'), true);
echo "Found " . count($products) . " products in source file\n\n";

// Get configuration
$channelCode = 'default';
$localeCode = 'en';

// Get attribute IDs
$attributes = DB::table('attributes')
    ->whereIn('code', ['name', 'price', 'description', 'short_description', 'url_key', 'status', 'cost', 'weight', 'special_price'])
    ->get()
    ->keyBy('code');

echo "Found " . count($attributes) . " attributes\n";
foreach ($attributes as $code => $attr) {
    echo "  - $code (ID: {$attr->id}, Type: {$attr->type})\n";
}
echo "\n";

// Get all products from database
echo "Loading products from database...\n";
$dbProducts = DB::table('products')->get()->keyBy('sku');
echo "Found " . count($dbProducts) . " products in database\n\n";

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
        if (!empty($sourceProduct['name']) && isset($attributes['name'])) {
            $attributeValues[] = [
                'product_id' => $productId,
                'attribute_id' => $attributes['name']->id,
                'locale' => $localeCode,
                'channel' => $channelCode,
                'text_value' => $sourceProduct['name'],
                'unique_id' => "{$channelCode}|{$localeCode}|{$productId}|{$attributes['name']->id}",
            ];
        }
        
        // Price (channel-specific, float)
        if (!empty($sourceProduct['price']) && isset($attributes['price'])) {
            $attributeValues[] = [
                'product_id' => $productId,
                'attribute_id' => $attributes['price']->id,
                'locale' => null,
                'channel' => $channelCode,
                'float_value' => (float) $sourceProduct['price'],
                'unique_id' => "{$channelCode}||{$productId}|{$attributes['price']->id}",
            ];
        }
        
        // Description (locale-specific, text)
        if (!empty($sourceProduct['long_description']) && isset($attributes['description'])) {
            $attributeValues[] = [
                'product_id' => $productId,
                'attribute_id' => $attributes['description']->id,
                'locale' => $localeCode,
                'channel' => $channelCode,
                'text_value' => $sourceProduct['long_description'],
                'unique_id' => "{$channelCode}|{$localeCode}|{$productId}|{$attributes['description']->id}",
            ];
        }
        
        // Short description (locale-specific, text)
        if (!empty($sourceProduct['short_description']) && isset($attributes['short_description'])) {
            $attributeValues[] = [
                'product_id' => $productId,
                'attribute_id' => $attributes['short_description']->id,
                'locale' => $localeCode,
                'channel' => $channelCode,
                'text_value' => $sourceProduct['short_description'],
                'unique_id' => "{$channelCode}|{$localeCode}|{$productId}|{$attributes['short_description']->id}",
            ];
        }
        
        // URL Key (locale-specific, text)
        $urlKey = $sourceProduct['handle'] ?: Str::slug($sourceProduct['name'] ?: $sku);
        if (isset($attributes['url_key'])) {
            $attributeValues[] = [
                'product_id' => $productId,
                'attribute_id' => $attributes['url_key']->id,
                'locale' => $localeCode,
                'channel' => $channelCode,
                'text_value' => $urlKey,
                'unique_id' => "{$channelCode}|{$localeCode}|{$productId}|{$attributes['url_key']->id}",
            ];
        }
        
        // Status (channel-specific, boolean)
        if (isset($attributes['status'])) {
            $attributeValues[] = [
                'product_id' => $productId,
                'attribute_id' => $attributes['status']->id,
                'locale' => null,
                'channel' => $channelCode,
                'boolean_value' => $sourceProduct['available'] ? 1 : 0,
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
        
        // Insert attribute values
        if (!empty($attributeValues)) {
            DB::table('product_attribute_values')->insert($attributeValues);
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
$totalAttributeValues = DB::table('product_attribute_values')->count();
$productsWithAttributes = DB::table('product_attribute_values')
    ->distinct('product_id')
    ->count('product_id');

echo "  Total attribute values: $totalAttributeValues\n";
echo "  Products with attributes: $productsWithAttributes\n";
echo "\nDone! Admin panel should now show product data.\n";
