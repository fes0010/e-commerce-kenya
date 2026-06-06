<?php

/**
 * Idempotent fix for scraped product data issues.
 * Safe to run on every container startup.
 */

require '/var/www/bagisto/vendor/autoload.php';

$app = require_once '/var/www/bagisto/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

// Fix 1: Strip leading "storage/" from product_images.path
// Scraped images were stored as "storage/products/xxx.jpg" but need "products/xxx.jpg"
$fixed = DB::table('product_images')
    ->where('path', 'like', 'storage/%')
    ->update(['path' => DB::raw("SUBSTRING(path, 9)")]);

echo "[fix-product-data] Fixed image paths: {$fixed} rows\n";

// Fix 2: Backfill missing boolean attribute rows in product_attribute_values
// Without these rows, the ProductRepository SQL JOINs return 0 results.
$booleanAttrs = [
    'status'               => 1, // enabled
    'visible_individually' => 1, // visible
    'new'                  => 0, // not new by default
    'featured'             => 0, // not featured by default
];

$productIds = DB::table('products')->pluck('id');

foreach ($booleanAttrs as $code => $defaultValue) {
    $attr = DB::table('attributes')->where('code', $code)->first();

    if (! $attr) {
        echo "[fix-product-data] WARNING: {$code} attribute not found\n";
        continue;
    }

    $existing = DB::table('product_attribute_values')
        ->where('attribute_id', $attr->id)
        ->pluck('product_id');

    $missing = $productIds->diff($existing);

    if ($missing->isEmpty()) {
        echo "[fix-product-data] {$code} attribute: all {$productIds->count()} rows present\n";
        continue;
    }

    $now = now();
    foreach ($missing->chunk(500) as $chunk) {
        $rows = $chunk->map(fn ($pid) => [
            'attribute_id'  => $attr->id,
            'product_id'    => $pid,
            'boolean_value' => $defaultValue,
        ])->values()->toArray();

        DB::table('product_attribute_values')->insert($rows);
    }

    echo "[fix-product-data] {$code} attribute: backfilled {$missing->count()} rows\n";
}

// Fix 3: Ensure all disabled status rows are enabled
$statusAttr = DB::table('attributes')->where('code', 'status')->first();
if ($statusAttr) {
    $enabled = DB::table('product_attribute_values')
        ->where('attribute_id', $statusAttr->id)
        ->where('boolean_value', '!=', 1)
        ->update(['boolean_value' => 1]);

    echo "[fix-product-data] Enabled products in attribute_values: {$enabled} rows\n";
}

// Fix 4: Sync product_flat boolean columns
DB::table('product_flat')->where('status', '!=', 1)->update(['status' => 1]);
DB::table('product_flat')->whereNull('new')->update(['new' => 0]);
DB::table('product_flat')->whereNull('featured')->update(['featured' => 0]);
echo "[fix-product-data] product_flat boolean columns synced\n";

// Fix 5: Backfill missing product_channels rows
// Without these, the ProductRepository query filter on channel_id matches nothing.
$channelId = DB::table('channels')->value('id') ?? 1;
$existingChannels = DB::table('product_channels')
    ->where('channel_id', $channelId)
    ->pluck('product_id');

$missingChannels = $productIds->diff($existingChannels);

if ($missingChannels->isNotEmpty()) {
    foreach ($missingChannels->chunk(500) as $chunk) {
        $rows = $chunk->map(fn ($pid) => [
            'product_id' => $pid,
            'channel_id' => $channelId,
        ])->values()->toArray();

        DB::table('product_channels')->insertOrIgnore($rows);
    }

    echo "[fix-product-data] product_channels: backfilled {$missingChannels->count()} rows\n";
} else {
    echo "[fix-product-data] product_channels: all {$productIds->count()} rows present\n";
}

// Fix 6: Assign category images from product images (for category carousel)
$categoriesWithoutLogo = DB::table('categories')
    ->where('status', 1)
    ->whereNull('logo_path')
    ->pluck('id');

$catImagesFixed = 0;
foreach ($categoriesWithoutLogo as $catId) {
    $prodId = DB::table('product_categories')->where('category_id', $catId)->value('product_id');
    if ($prodId) {
        $imgPath = DB::table('product_images')->where('product_id', $prodId)->value('path');
        if ($imgPath) {
            DB::table('categories')->where('id', $catId)->update(['logo_path' => $imgPath]);
            $catImagesFixed++;
        }
    }
}

echo "[fix-product-data] Category logos assigned: {$catImagesFixed} categories\n";

