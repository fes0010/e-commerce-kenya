<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Webkul\Attribute\Repositories\AttributeRepository;
use Webkul\Attribute\Repositories\AttributeOptionRepository;
use Webkul\Attribute\Repositories\AttributeFamilyRepository;
use Webkul\Product\Models\Product;
use Illuminate\Support\Facades\DB;

class PopulateProductData extends Command
{
    protected $signature = 'bagisto:populate-data {--force : Overwrite existing attribute values}';
    protected $description = 'Populate product attributes, options, values and relations';

    public function handle(
        AttributeRepository $attributeRepository,
        AttributeOptionRepository $attributeOptionRepository,
        AttributeFamilyRepository $attributeFamilyRepository
    ) {
        $this->info('Starting population...');

        // ─── 1. Attribute definitions ────────────────────────────────────────
        $attributesConfig = [
            'size'  => ['S', 'M', 'L', 'XL', 'XXL'],
            'color' => ['Red', 'Blue', 'Green', 'Black', 'White', 'Yellow'],
            'brand' => ['Nike', 'Adidas', 'Puma', 'Gucci', 'Zara', 'H&M'],
            'age'   => ['Kids', 'Teens', 'Adults'],
        ];

        $createdAttributes = [];

        foreach ($attributesConfig as $code => $options) {
            $attribute = $attributeRepository->findOneByField('code', $code);

            if (! $attribute) {
                $attribute = $attributeRepository->create([
                    'code'                => $code,
                    'admin_name'          => ucfirst($code),
                    'type'                => 'select',
                    'validation'          => '',
                    'position'            => 1,
                    'is_required'         => 0,
                    'is_unique'           => 0,
                    'value_per_locale'    => 0,
                    'value_per_channel'   => 0,
                    'is_filterable'       => 1,
                    'is_configurable'     => 1,
                    'is_user_defined'     => 1,
                    'is_visible_on_front' => 1,
                    'is_comparable'       => 1,
                    'en'                  => ['name' => ucfirst($code)],
                ]);
                $this->info("  ✓ Created attribute: $code (ID {$attribute->id})");
            } else {
                // Ensure existing attributes have correct flags
                DB::table('attributes')->where('id', $attribute->id)->update([
                    'is_filterable'       => 1,
                    'is_configurable'     => 1,
                    'is_visible_on_front' => 1,
                ]);
                $this->info("  ✓ Attribute already exists: $code (ID {$attribute->id})");
            }

            // ── Options ───────────────────────────────────────────────────────
            $optionIds = [];
            foreach ($options as $index => $optionName) {
                $existingOption = $attribute->options()->where('admin_name', $optionName)->first();
                if (! $existingOption) {
                    $existingOption = $attributeOptionRepository->create([
                        'admin_name'   => $optionName,
                        'sort_order'   => $index,
                        'attribute_id' => $attribute->id,
                        'en'           => ['label' => $optionName],
                    ]);
                }
                $optionIds[] = $existingOption->id;
            }

            $createdAttributes[$code] = [
                'attribute' => $attribute->fresh(),
                'optionIds' => $optionIds,
            ];
        }

        // ─── 2. Assign to attribute family ───────────────────────────────────
        $family = $attributeFamilyRepository->find(1);
        if ($family) {
            // Pick the "general" group or fall back to first group
            $groupId = DB::table('attribute_groups')
                ->where('attribute_family_id', $family->id)
                ->where('code', 'general')
                ->value('id');

            if (! $groupId) {
                $groupId = DB::table('attribute_groups')
                    ->where('attribute_family_id', $family->id)
                    ->value('id');
            }

            $this->info("  Using attribute group ID: $groupId");

            if ($groupId) {
                foreach ($createdAttributes as $code => $data) {
                    $exists = DB::table('attribute_group_mappings')
                        ->where('attribute_id', $data['attribute']->id)
                        ->where('attribute_group_id', $groupId)
                        ->exists();

                    if (! $exists) {
                        $maxPos = DB::table('attribute_group_mappings')
                            ->where('attribute_group_id', $groupId)
                            ->max('position') ?? 0;

                        DB::table('attribute_group_mappings')->insert([
                            'attribute_id'       => $data['attribute']->id,
                            'attribute_group_id' => $groupId,
                            'position'           => $maxPos + 1,
                        ]);
                        $this->info("  ✓ Mapped $code to family group $groupId");
                    }
                }
            }
        }

        // ─── 3. Set attribute values on every product ─────────────────────────
        $products = Product::all();
        $total    = $products->count();

        if ($total === 0) {
            $this->warn('  ⚠ No products found in database — run this command on your production server!');
        } else {
            $this->info("  Updating $total products...");
        }

        $force = $this->option('force');
        $bar   = $this->output->createProgressBar($total);
        $bar->start();

        foreach ($products as $product) {
            foreach ($createdAttributes as $code => $data) {
                $attribute = $data['attribute'];

                $existing = DB::table('product_attribute_values')
                    ->where('product_id', $product->id)
                    ->where('attribute_id', $attribute->id)
                    ->first();

                // Pick a random option
                $optionId = $data['optionIds'][array_rand($data['optionIds'])];

                if ($existing && ! $force) {
                    // Already set — skip unless --force
                    continue;
                }

                if ($existing) {
                    DB::table('product_attribute_values')
                        ->where('id', $existing->id)
                        ->update(['integer_value' => $optionId]);
                } else {
                    DB::table('product_attribute_values')->insert([
                        'product_id'    => $product->id,
                        'attribute_id'  => $attribute->id,
                        'channel'       => null,
                        'locale'        => null,
                        'integer_value' => $optionId,
                    ]);
                }
            }

            // ── Relations (same category) ─────────────────────────────────────
            $categoryIds = $product->categories->pluck('id')->toArray();

            $baseQuery = ! empty($categoryIds)
                ? Product::whereHas('categories', fn ($q) => $q->whereIn('category_id', $categoryIds))
                         ->where('id', '!=', $product->id)
                : Product::where('id', '!=', $product->id);

            $product->related_products()->sync(
                (clone $baseQuery)->inRandomOrder()->limit(6)->pluck('id')->toArray()
            );
            $product->up_sells()->sync(
                (clone $baseQuery)->inRandomOrder()->limit(4)->pluck('id')->toArray()
            );
            $product->cross_sells()->sync(
                (clone $baseQuery)->inRandomOrder()->limit(4)->pluck('id')->toArray()
            );

            $bar->advance();
        }

        $bar->finish();
        $this->newLine();

        // ─── 4. Sync product_flat table ──────────────────────────────────────
        $this->info('  Syncing product_flat...');
        try {
            $this->call('product:price-index');
        } catch (\Exception $e) {
            // Not all Bagisto versions have this — skip silently
        }

        // ─── 5. Reindex ──────────────────────────────────────────────────────
        $this->info('  Reindexing...');
        try {
            $this->call('indexer:index');
        } catch (\Exception $e) {
            $this->warn('  ⚠ Indexer not available: ' . $e->getMessage());
        }

        // ─── 6. Verify ───────────────────────────────────────────────────────
        $valueCount = DB::table('product_attribute_values')
            ->whereIn('attribute_id', collect($createdAttributes)->pluck('attribute.id'))
            ->count();

        $this->info('');
        $this->info("✅ Done! Attribute values written: $valueCount");
        $this->info('   Run `php artisan cache:clear` if filters still do not appear.');
    }
}
