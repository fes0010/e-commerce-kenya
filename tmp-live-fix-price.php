<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$priceAttrId = DB::table('attributes')->where('code', 'price')->value('id');

if (! $priceAttrId) {
    echo "price attribute not found\n";
    exit(1);
}

$sql = "
    INSERT INTO product_attribute_values (locale, channel, float_value, product_id, attribute_id, unique_id)
    SELECT NULL, NULL, pf.price, pf.product_id, ?, CONCAT(pf.product_id, '|', ?)
    FROM product_flat pf
    LEFT JOIN product_attribute_values pav
        ON pav.product_id = pf.product_id
       AND pav.attribute_id = ?
    WHERE pav.id IS NULL
      AND pf.price IS NOT NULL
    GROUP BY pf.product_id, pf.price
";

DB::statement($sql, [$priceAttrId, $priceAttrId, $priceAttrId]);

$count = DB::table('product_attribute_values')
    ->where('attribute_id', $priceAttrId)
    ->count();

$sample = DB::table('product_attribute_values')
    ->where('attribute_id', $priceAttrId)
    ->select('product_id', 'float_value', 'unique_id')
    ->limit(5)
    ->get();

echo "price_attr_id={$priceAttrId} | count={$count}\n";

foreach ($sample as $row) {
    echo "product={$row->product_id} | float={$row->float_value} | unique_id={$row->unique_id}\n";
}
