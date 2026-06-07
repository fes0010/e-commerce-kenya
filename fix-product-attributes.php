<?php
/**
 * Fix missing product attribute values
 * This script populates the product_attribute_values and product_flat tables
 * with data from the source JSON file
 */

require '/var/www/bagisto/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

$app = require_once '/var/www/bagisto/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "========================================\n";
echo "Fix Product Attribute Values\n";
echo "========================================\n\n";

// Load source data
echo "Loading product data...\n";
$products = json_decode(file_get_contents('/tmp/peekaboo_products.json'), true);
echo "Found " . count($products) . " products in source file\n\n";

// Get configuration
$channelId = 1;
$localeCode = 'en';
$localeId = 1;

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
        
        // Skip products that already have attribute values
        $hasAttributes = DB::table('product_attribute_values')
            ->where('product_id', $productId)
            ->exists();
        
        if ($hasAttributes) {
            $skipped++;
            continue;
        }
        
        // Also skip if product doesn't exist in database
        if (!isset($dbProducts[$sku])) {
            $skipped++;
            continue;
        }
        
        // Create attribute values
        $attributeValues = [];
        $timestamp = now();
        
        // Name
        if (!empty($sourceProduct['name'])) {
            $attributeValues[] = [
                'product_id' => $productId,
                'attribute_id' => $attributes['name'],
                'locale' => $localeCode,
                'channel' => 'default',
                'text_value' => $sourceProduct['name'],
                'unique_id' => "default|{$localeCode}|{$productId}|{$attributes['name']}",
            ];
        }
        
        // Price
        if (!empty($sourceProduct['price'])) {
            $attributeValues[] = [
                'product_id' => $productId,
                'attribute_id' => $attributes['price'],
                'locale' => null,
                'channel' => 'default',
                'float_value' => (float) $sourceProduct['price'],
                'unique_id' => "default||{$productId}|{$attributes['price']}",
            ];
        }
        
        // Description
        if (!empty($sourceProduct['long_description'])) {
            $attributeValues[] = [
                'product_id' => $productId,
                'attribute_id' => $attributes['description'],
                'locale' => $localeCode,
                'channel' => 'default',
                'text_value' => $sourceProduct['long_description'],
                'unique_id' => "default|{$localeCode}|{$productId}|{$attributes['description']}",
            ];
        }
        
        // Short description
        if (!empty($sourceProduct['short_description'])) {
            $attributeValues[] = [
                'product_id' => $productId,
                'attribute_id' => $attributes['short_description'],
                'locale' => $localeCode,
                'channel' => 'default',
                'text_value' => $sourceProduct['short_description'],
                'unique_id' => "default|{$localeCode}|{$productId}|{$attributes['short_description']}",
            ];
        }
        
        // URL Key
        $urlKey = $sourceProduct['handle'] ?: Str::slug($sourceProduct['name'] ?: $sku);
        $attributeValues[] = [
            'product_id' => $productId,
            'attribute_id' => $attributes['url_key'],
            'locale' => $localeCode,
            'channel' => 'default',
            'text_value' => $urlKey,
            'unique_id' => "default|{$localeCode}|{$productId}|{$attributes['url_key']}",
        ];
        
        // Status
        $attributeValues[] = [
            'product_id' => $productId,
            'attribute_id' => $attributes['status'],
            'locale' => null,
            'channel' => 'default',
            'boolean_value' => $sourceProduct['available'] ? 1 : 0,
            'unique_id' => "default||{$productId}|{$attributes['status']}",
        ];
        
        // Insert attribute values if we have any
        if (!empty($attributeValues)) {
            DB::table('product_attribute_values')->insert($attributeValues);
            $processed++;
        }
        
    } catch (\Exception $e) {
        echo "\nError processing product: " . $e->getMessage() . "\n";
        $errors++;
    }
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
$productsWithNames = DB::table('product_flats')
    ->whereNotNull('name')
    ->where('name', '!=', '')
    ->count();

echo "  Total attribute values: $totalAttributeValues\n";
echo "  Products with attributes: $productsWithAttributes\n";
echo "  Products with names: $productsWithNames\n";
echo "\nDone!\n";
