<?php

namespace Database\Seeders;

use App\Models\AttributeDefinition;
use App\Models\AttributeValue;
use App\Models\Brand;
use App\Models\Category;
use App\Models\InventoryLocation;
use App\Models\InventoryLevel;
use App\Models\MediaAsset;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::firstOrCreate(
            ['slug' => 'lighting'],
            ['name' => 'Lighting', 'is_active' => true]
        );

        $brand = Brand::firstOrCreate(
            ['slug' => 'otex-design'],
            ['name' => 'Otex Design', 'is_active' => true]
        );

        $slugBase = 'lumina-desk-lamp';
        $generateSuffix = fn () => strtoupper(Str::random(4));

        $slug = "{$slugBase}-{$generateSuffix()}";
        while (Product::where('slug', $slug)->orWhere('slug_translations->en', $slug)->exists()) {
            $slug = "{$slugBase}-{$generateSuffix()}";
        }

        $slugTranslations = [
            'en' => $slug,
            'ar' => Str::slug('مصباح لومينا ' . substr($slug, -4)) ?: $slug,
        ];

        $sku = 'LUMINA-' . strtoupper(Str::random(5));
        while (Product::where('sku', $sku)->exists()) {
            $sku = 'LUMINA-' . strtoupper(Str::random(5));
        }

        if (Product::where('slug_translations->en', $slugTranslations['en'])->exists()) {
            return;
        }

        $product = Product::create([
            'category_id' => $category->id,
            'brand_id' => $brand->id,
            'name' => 'Lumina Desk Lamp',
            'slug' => $slug,
            'summary' => 'Minimalist LED lamp with adaptive light and wireless charging',
            'description' => 'Lumina brings sculptural lighting to your desktop with multi-point illumination and color-tuned finishes.',
            'price' => 129.00,
            'compare_at_price' => 159.00,
            'rating' => 4.8,
            'reviews_count' => 124,
            'sku' => $sku,
            'stock' => 40,
            'reserved_stock' => 0,
            'is_active' => true,
            'weight_grams' => 1850,
            'image' => null,
            'gallery' => null,
            'images' => null,
            'colors' => ['Sand', 'Sage'],
            'name_translations' => ['en' => 'Lumina Desk Lamp', 'ar' => 'مصباح لومينا'],
            'slug_translations' => $slugTranslations,
            'summary_translations' => ['en' => 'Minimalist LED lamp with adaptive light and wireless charging'],
            'description_translations' => ['en' => 'Lumina brings sculptural lighting to your desktop with multi-point illumination and color-tuned finishes.'],
            'features' => ['Adaptive light sensor', 'USB-C + wireless charging', 'Color-matched metal finishes'],
            'shipping_returns' => [
                'Ships in 1-2 business days.',
                'Free 30-day returns on unused items.',
                'Warranty support included.',
            ],
            'seo_title_translations' => ['en' => 'Lumina Desk Lamp | Otex'],
            'seo_description_translations' => ['en' => 'Shop the Lumina Desk Lamp with smart light, multiple finishes, and streaming-ready features.'],
        ]);

        $product->categories()->syncWithoutDetaching([$category->id]);

        $colorOption = $product->options()->create([
            'code' => 'color',
            'name_translations' => ['en' => 'Color', 'ar' => 'اللون'],
            'position' => 1,
        ]);

        $sand = $colorOption->values()->create([
            'value' => 'sand',
            'label_translations' => ['en' => 'Sand'],
            'swatch_hex' => '#E9DCC7',
            'position' => 1,
        ]);

        $sage = $colorOption->values()->create([
            'value' => 'sage',
            'label_translations' => ['en' => 'Sage'],
            'swatch_hex' => '#9BB7A7',
            'position' => 2,
        ]);

        $sizeOption = $product->options()->create([
            'code' => 'size',
            'name_translations' => ['en' => 'Size', 'ar' => 'الحجم'],
            'position' => 2,
        ]);

        $small = $sizeOption->values()->create([
            'value' => 'small',
            'label_translations' => ['en' => 'Small'],
            'position' => 1,
        ]);

        $large = $sizeOption->values()->create([
            'value' => 'large',
            'label_translations' => ['en' => 'Large'],
            'position' => 2,
        ]);

        $variantData = [
            ['base_sku' => 'LUM-SAND-S', 'color' => $sand, 'size' => $small, 'price' => 12900, 'has_sensor' => true],
            ['base_sku' => 'LUM-SAND-L', 'color' => $sand, 'size' => $large, 'price' => 13900],
            ['base_sku' => 'LUM-SAGE-S', 'color' => $sage, 'size' => $small, 'price' => 12900],
            ['base_sku' => 'LUM-SAGE-L', 'color' => $sage, 'size' => $large, 'price' => 13900],
        ];

        $location = InventoryLocation::firstOrCreate(
            ['code' => 'MAIN'],
            ['name' => 'Main warehouse', 'address' => 'Primary fulfillment center']
        );

        $mediaUrls = [
            ['url' => 'https://cdn.example.com/lumina-sand.jpg', 'option_value_id' => $sand->id, 'is_primary' => true],
            ['url' => 'https://cdn.example.com/lumina-sage.jpg', 'option_value_id' => $sage->id],
            ['url' => 'https://cdn.example.com/lumina-lifestyle.jpg'],
        ];

        $variantModels = [];

        $generateSku = fn (string $base) => strtoupper($base . '-' . Str::random(3));

        foreach ($variantData as $index => $variantRow) {
            $variantSku = $generateSku($variantRow['base_sku']);
            while (ProductVariant::where('sku', $variantSku)->exists()) {
                $variantSku = $generateSku($variantRow['base_sku']);
            }

            $variant = $product->variants()->create([
                'sku' => $variantSku,
                'gtin' => 'GTIN' . rand(100000, 999999),
                'mpn' => 'MPN-' . strtoupper(substr($variantRow['base_sku'], 0, 6)),
                'has_sensor' => $variantRow['has_sensor'] ?? false,
                'currency' => 'USD',
                'price_cents' => $variantRow['price'],
                'compare_at_cents' => $variantRow['price'] + 2000,
                'cost_cents' => $variantRow['price'] - 3000,
                'sale_cents' => isset($variantRow['has_sensor']) && $variantRow['has_sensor'] ? $variantRow['price'] - 1500 : null,
                'sale_starts_at' => now()->subDays(2),
                'sale_ends_at' => now()->addDays(10),
                'weight_grams' => 1800 + ($index * 50),
                'length_mm' => 420,
                'width_mm' => 160,
                'height_mm' => 380,
                'track_inventory' => true,
                'allow_backorder' => false,
                'low_stock_threshold' => 5,
                'metadata' => ['finish' => $variantRow['color']->value, 'size' => $variantRow['size']->value],
            ]);

            $variant->optionValues()->sync([$variantRow['color']->id, $variantRow['size']->id]);

            $variant->inventoryLevels()->create([
                'location_id' => $location->id,
                'on_hand' => 12 - $index * 2,
                'reserved' => 0,
            ]);

            $variant->load('optionValues');

            $variantModels[] = $variant;
        }

        foreach ($mediaUrls as $index => $asset) {
            $variantMatch = null;
            if (!empty($asset['option_value_id'])) {
                $variantMatch = collect($variantModels)->first(fn (ProductVariant $variant) => $variant->optionValues->contains('id', $asset['option_value_id']));
            }

            MediaAsset::create([
                'product_id' => $product->id,
                'url' => $asset['url'],
                'option_value_id' => $asset['option_value_id'] ?? null,
                'variant_id' => $variantMatch?->id,
                'type' => 'image',
                'alt_text' => 'Lumina lifestyle',
                'position' => $index,
                'is_primary' => $asset['is_primary'] ?? false,
            ]);
        }

        $this->seedAttribute('material', $product->id, null, ['text_value' => 'Anodized aluminum & frosted glass']);
        $this->seedAttribute('connectivity', $product->id, null, ['json_value' => ['Wi-Fi', 'Bluetooth']]);
        $this->seedAttribute('sensor_type', null, $variantModels[0]->id ?? null, ['text_value' => 'Adaptive ambient sensor']);

        $product->update([
            'image' => $mediaUrls[0]['url'],
            'thumbnail' => $mediaUrls[0]['url'],
            'gallery' => json_encode(array_column($mediaUrls, 'url')),
            'images' => json_encode(array_column($mediaUrls, 'url')),
        ]);
    }

    private function seedAttribute(string $key, ?int $productId, ?int $variantId, array $values): void
    {
        $definition = AttributeDefinition::firstWhere('key', $key);
        if (!$definition) {
            return;
        }

        AttributeValue::updateOrCreate(
            [
                'definition_id' => $definition->id,
                'product_id' => $productId,
                'variant_id' => $variantId,
            ],
            $values
        );
    }
}
