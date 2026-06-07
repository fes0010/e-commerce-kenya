<?php
/**
 * Simple script to populate product_attribute_values from product_flat
 * Processes 100 products at a time
 */

require '/var/www/bagisto/vendor/autoload.php';

use Illuminate\Support\Facades\DB;

$app = require_once '/var/www/bagisto/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "Populating product attribute values...\n\n";

// Get attribute IDs
$nameAttr = DB::table('attributes')->where('code', 'name')->first();
$priceAttr = DB::table('attributes')->where('code', 'price')->first();
$descAttr = DB::table('attributes')->where('code', 'description')->first();
$shortDescAttr = DB::table('attributes')->where('code', 'short_description')->first();
$urlKeyAttr = DB::table('attributes')->where('code', 'url_key')->first();
$statusAttr = DB::table('attributes')->where('code', 'status')->first();

echo "Processing 100 products...\n";

// Get first 100 products that need fixing
$products = DB::table('product_flat')
    ->select('product_id', 'name', 'price', 'description', 'short_description', 'url_key', 'status')
    ->where('channel', 'default')
    ->where('locale', 'en')
    ->whereNotNull('name')
    ->where('name', '!=', '')
    ->limit(100)
    ->get();

$processed = 0;

foreach ($products as $product) {
    // Check if already has attributes
    $exists = DB::table('product_attribute_values')
        ->where('product_id', $product->product_id)
        ->exists();
    
    if ($exists) {
        continue;
    }
    
    $attrs = [];
    
    // Name
    if ($product->name && $nameAttr) {
        $attrs[] = [
            'product_id' => $product->product_id,
            'attribute_id' => $nameAttr->id,
            'locale' => 'en',
            'channel' => 'default',
            'text_value' => $product->name,
            'unique_id' => "default|en|{$product->product_id}|{$nameAttr->id}",
        ];
    }
    
    // Price
    if ($product->price && $priceAttr) {
        $attrs[] = [
            'product_id' => $product->product_id,
            'attribute_id' => $priceAttr->id,
            'channel' => 'default',
            'float_value' => (float) $product->price,
            'unique_id' => "default||{$product->product_id}|{$priceAttr->id}",
        ];
    }
    
    // Description
    if ($product->description && $descAttr) {
        $attrs[] = [
            'product_id' => $product->product_id,
            'attribute_id' => $descAttr->id,
            'locale' => 'en',
            'channel' => 'default',
            'text_value' => $product->description,
            'unique_id' => "default|en|{$product->product_id}|{$descAttr->id}",
        ];
    }
    
    // Short description
    if ($product->short_description && $shortDescAttr) {
        $attrs[] = [
            'product_id' => $product->product_id,
            'attribute_id' => $shortDescAttr->id,
            'locale' => 'en',
            'channel' => 'default',
            'text_value' => $product->short_description,
            'unique_id' => "default|en|{$product->product_id}|{$shortDescAttr->id}",
        ];
    }
    
    // URL Key
    if ($product->url_key && $urlKeyAttr) {
        $attrs[] = [
            'product_id' => $product->product_id,
            'attribute_id' => $urlKeyAttr->id,
            'locale' => 'en',
            'channel' => 'default',
            'text_value' => $product->url_key,
            'unique_id' => "default|en|{$product->product_id}|{$urlKeyAttr->id}",
        ];
    }
    
    // Status
    if (isset($product->status) && $statusAttr) {
        $attrs[] = [
            'product_id' => $product->product_id,
            'attribute_id' => $statusAttr->id,
            'channel' => 'default',
            'boolean_value' => $product->status ? 1 : 0,
            'unique_id' => "default||{$product->product_id}|{$statusAttr->id}",
        ];
    }
    
    if (!empty($attrs)) {
        DB::table('product_attribute_values')->insert($attrs);
        $processed++;
    }
}

echo "\nProcessed: $processed products\n";
echo "Total attribute values: " . DB::table('product_attribute_values')->count() . "\n";
echo "\nDone!\n";
