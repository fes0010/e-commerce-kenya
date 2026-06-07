#!/bin/bash

# Product Data Diagnostic Script
# This script checks the state of product data in Bagisto

CONTAINER_ID="d95fffc3b1d9"

echo "========================================="
echo "Bagisto Product Data Diagnostic"
echo "========================================="
echo ""

echo "1. Database Table Counts:"
echo "-------------------------"
docker exec $CONTAINER_ID php artisan tinker --execute="
echo 'Products: ' . \Webkul\Product\Models\Product::count() . PHP_EOL;
echo 'Product Flats: ' . \Webkul\Product\Models\ProductFlat::count() . PHP_EOL;
echo 'Product Images: ' . \Webkul\Product\Models\ProductImage::count() . PHP_EOL;
echo 'Product Attribute Values: ' . \Webkul\Product\Models\ProductAttributeValue::count() . PHP_EOL;
echo 'Product Categories: ' . DB::table('product_categories')->count() . PHP_EOL;
echo 'Product Inventories: ' . \Webkul\Product\Models\ProductInventory::count() . PHP_EOL;
"

echo ""
echo "2. Sample Product Data:"
echo "----------------------"
docker exec $CONTAINER_ID php artisan tinker --execute="
\$product = \Webkul\Product\Models\Product::with(['images', 'categories'])->first();
if (\$product) {
    echo 'Product ID: ' . \$product->id . PHP_EOL;
    echo 'SKU: ' . \$product->sku . PHP_EOL;
    echo 'Type: ' . \$product->type . PHP_EOL;
    echo 'Images: ' . \$product->images->count() . PHP_EOL;
    echo 'Categories: ' . \$product->categories->count() . PHP_EOL;
    
    \$flat = \Webkul\Product\Models\ProductFlat::where('product_id', \$product->id)->first();
    if (\$flat) {
        echo 'Flat Name: ' . (\$flat->name ?? 'NULL') . PHP_EOL;
        echo 'Flat Price: ' . (\$flat->price ?? 'NULL') . PHP_EOL;
        echo 'Flat Description: ' . (substr(\$flat->description ?? 'NULL', 0, 50)) . PHP_EOL;
    }
}
"

echo ""
echo "3. Attribute Configuration:"
echo "--------------------------"
docker exec $CONTAINER_ID php artisan tinker --execute="
\$attributes = \Webkul\Attribute\Models\Attribute::whereIn('code', ['name', 'price', 'description', 'short_description', 'sku', 'status', 'url_key'])->get(['id', 'code', 'type', 'is_required']);
foreach (\$attributes as \$attr) {
    echo \$attr->code . ' (ID: ' . \$attr->id . ', Type: ' . \$attr->type . ', Required: ' . (\$attr->is_required ? 'Yes' : 'No') . ')' . PHP_EOL;
}
"

echo ""
echo "4. Channel & Locale Info:"
echo "------------------------"
docker exec $CONTAINER_ID php artisan tinker --execute="
\$channel = \Webkul\Core\Models\Channel::first();
\$locale = \Webkul\Core\Models\Locale::first();
echo 'Channel: ' . \$channel->code . ' (ID: ' . \$channel->id . ')' . PHP_EOL;
echo 'Locale: ' . \$locale->code . ' (ID: ' . \$locale->id . ')' . PHP_EOL;
"

echo ""
echo "5. Import History:"
echo "-----------------"
docker exec $CONTAINER_ID php artisan tinker --execute="
\$importBatches = DB::table('import_batches')->count();
echo 'Import Batches: ' . \$importBatches . PHP_EOL;
"

echo ""
echo "6. Sample Product SKUs (first 10):"
echo "----------------------------------"
docker exec $CONTAINER_ID php artisan tinker --execute="
\$products = \Webkul\Product\Models\Product::take(10)->get(['id', 'sku', 'type']);
foreach (\$products as \$p) {
    echo 'ID: ' . \$p->id . ', SKU: ' . \$p->sku . ', Type: ' . \$p->type . PHP_EOL;
}
"

echo ""
echo "========================================="
echo "Diagnostic Complete"
echo "========================================="
echo ""
echo "STATUS:"
docker exec $CONTAINER_ID php artisan tinker --execute="
\$withNames = DB::table('product_flat')->whereNotNull('name')->where('name', '!=', '')->count();
\$total = DB::table('products')->count();
if (\$withNames == \$total) {
    echo '✅ ALL PRODUCTS HAVE DATA!' . PHP_EOL;
} else {
    echo '⚠️  WARNING: ' . (\$total - \$withNames) . ' products missing data!' . PHP_EOL;
}
"
echo ""
echo "See PRODUCT-FIX-COMPLETE.md for details"
