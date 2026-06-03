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
    ->update(['path' => DB::raw('SUBSTRING(path, 9)')]);

echo "[fix-product-data] Fixed image paths: {$fixed} rows\n";

// Fix 2: Enable all products in product_attribute_values (boolean_value for status attribute)
$statusAttr = DB::table('attributes')->where('code', 'status')->first();

if ($statusAttr) {
    $enabled = DB::table('product_attribute_values')
        ->where('attribute_id', $statusAttr->id)
        ->where('boolean_value', '!=', 1)
        ->update(['boolean_value' => 1]);

    echo "[fix-product-data] Enabled products in attribute_values: {$enabled} rows\n";
} else {
    echo "[fix-product-data] WARNING: status attribute not found\n";
}

// Fix 3: Sync status=1 into product_flat
$flatUpdated = DB::table('product_flat')
    ->where('status', '!=', 1)
    ->update(['status' => 1]);

echo "[fix-product-data] Enabled products in product_flat: {$flatUpdated} rows\n";
