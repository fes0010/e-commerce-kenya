<?php

/**
 * Database script to update special prices to be exactly 40% less than original prices (40% discount).
 * Run: php fix-special-prices.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Kernel::class)->bootstrap();

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

echo "====================================================\n";
echo "Applying 40% Discount (Special Prices) to all Products\n";
echo "====================================================\n\n";

// Find attribute IDs
$specialPriceAttrId = DB::table('attributes')->where('code', 'special_price')->value('id');

if (! $specialPriceAttrId) {
    echo "ERROR: 'special_price' attribute not found in attributes table.\n";
    exit(1);
}

echo "Found 'special_price' attribute ID: {$specialPriceAttrId}\n";

// Get all products from product_flat that have a price
$products = DB::table('product_flat')
    ->whereNotNull('price')
    ->where('price', '>', 0)
    ->select('id', 'product_id', 'sku', 'name', 'price', 'special_price')
    ->get();

echo 'Found '.$products->count()." products with original prices.\n\n";

$updatedCount = 0;

DB::beginTransaction();

try {
    foreach ($products as $index => $product) {
        $originalPrice = (float) $product->price;
        // 40% less than original price -> special_price = original_price * 0.60
        $newSpecialPrice = round($originalPrice * 0.60, 2);

        // Update product_flat
        DB::table('product_flat')
            ->where('product_id', $product->product_id)
            ->update([
                'special_price' => $newSpecialPrice,
                'special_price_from' => null,
                'special_price_to' => null,
            ]);

        // Update or insert in product_attribute_values (EAV)
        // Unique ID format for special_price is "{product_id}|{special_price_attribute_id}"
        $uniqueId = "{$product->product_id}|{$specialPriceAttrId}";

        DB::table('product_attribute_values')->updateOrInsert(
            [
                'product_id' => $product->product_id,
                'attribute_id' => $specialPriceAttrId,
            ],
            [
                'locale' => null,
                'channel' => null,
                'float_value' => $newSpecialPrice,
                'unique_id' => $uniqueId,
                'text_value' => null,
                'boolean_value' => null,
                'integer_value' => null,
                'datetime_value' => null,
                'date_value' => null,
                'json_value' => null,
            ]
        );

        $updatedCount++;

        if ($updatedCount <= 10 || ($index + 1) % 100 == 0) {
            echo sprintf("  [%d/%d] SKU: %s | Original: %.2f | New Special: %.2f\n",
                $index + 1, $products->count(), $product->sku, $originalPrice, $newSpecialPrice);
        }
    }

    DB::commit();
    echo "\nSuccessfully updated {$updatedCount} products in the database.\n";

} catch (Exception $e) {
    DB::rollBack();
    echo 'ERROR during update: '.$e->getMessage()."\n";
    exit(1);
}

// Clear and cache configuration
echo "\nClearing application caches...\n";
try {
    // Run optimize clear and cache
    Artisan::call('optimize:clear');
    echo "  - Caches cleared.\n";
    Artisan::call('optimize');
    echo "  - Caches optimized.\n";
} catch (Exception $e) {
    echo '  - WARNING: Failed to clear/warm caches: '.$e->getMessage()."\n";
}

// Elasticsearch reindex
echo "Reindexing search elements...\n";
try {
    Artisan::call('indexer:index');
    echo "  - Reindex completed successfully.\n";
} catch (Exception $e) {
    echo '  - WARNING: Failed to run indexer: '.$e->getMessage()."\n";
    echo "  - You may need to run 'php artisan indexer:index' manually.\n";
}

echo "\nDone!\n";
