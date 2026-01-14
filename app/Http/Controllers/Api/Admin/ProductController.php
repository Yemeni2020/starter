<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpsertProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\AttributeDefinition;
use App\Models\AttributeValue;
use App\Models\InventoryLocation;
use App\Models\MediaAsset;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with([
            'brand',
            'categories',
            'options.values',
            'variants.optionValues.option',
            'variants.inventoryLevels.location',
            'mediaAssets',
            'attributeValues.definition',
        ])->paginate($request->input('per_page', 15));

        return ProductResource::collection($products);
    }

    public function show(string $identifier)
    {
        $locale = app()->getLocale();

        $product = Product::with([
            'brand',
            'categories',
            'options.values',
            'variants.optionValues.option',
            'variants.inventoryLevels.location',
            'mediaAssets',
            'attributeValues.definition',
        ])
            ->where(function ($query) use ($identifier, $locale) {
                $query->where('id', $identifier)
                    ->orWhere('slug', $identifier)
                    ->orWhereRaw("json_extract(slug_translations, '$.\"{$locale}\"') = ?", [$identifier]);
            })
            ->firstOrFail();

        return new ProductResource($product);
    }

    public function store(UpsertProductRequest $request)
    {
        $payload = $request->validated();

        $product = DB::transaction(function () use ($payload) {
            $locales = config('app.supported_locales', ['ar', 'en']);
            $defaultLocale = config('app.locale', 'ar');
            $translationPayload = $this->prepareTranslationPayload($payload, $locales, $defaultLocale);

            $data = Arr::except($payload, ['options', 'variants', 'media', 'attributes', 'category_ids']);
            $sku = $payload['sku'] ?? data_get($payload, 'variants.0.sku');
            $productData = array_merge($data, $translationPayload, [
                'category_id' => $this->resolvePrimaryCategory($payload),
                'is_active' => isset($payload['is_active']) ? (bool) $payload['is_active'] : true,
                'sku' => $sku,
                'stock' => $payload['stock'] ?? 0,
                'reserved_stock' => $payload['reserved_stock'] ?? 0,
            ]);

            $product = Product::create($productData);
            $this->syncCategories($product, $payload['category_ids'] ?? []);
            $optionLookup = $this->syncOptions($product, $payload['options'] ?? []);
            $variants = $this->syncVariants($product, $payload['variants'] ?? [], $optionLookup);
            $this->syncMedia($product, $payload['media'] ?? [], $variants, $optionLookup);
            $this->syncAttributes($product, $payload['attributes'] ?? [], $variants);
            $this->updateBackfilledFields($product, $variants);

            return $product->fresh();
        });

        return new ProductResource($product->load([
            'brand',
            'categories',
            'options.values',
            'variants.optionValues.option',
            'variants.inventoryLevels.location',
            'mediaAssets',
            'attributeValues.definition',
        ]));
    }

    public function update(UpsertProductRequest $request, Product $product)
    {
        $payload = $request->validated();

        $product = DB::transaction(function () use ($payload, $product) {
            $locales = config('app.supported_locales', ['ar', 'en']);
            $defaultLocale = config('app.locale', 'ar');
            $translationPayload = $this->prepareTranslationPayload($payload, $locales, $defaultLocale, $product);

            $data = Arr::except($payload, ['options', 'variants', 'media', 'attributes', 'category_ids']);
            $sku = $payload['sku'] ?? data_get($payload, 'variants.0.sku');
            $productData = array_merge($data, $translationPayload, [
                'category_id' => $this->resolvePrimaryCategory($payload, $product),
                'is_active' => isset($payload['is_active']) ? (bool) $payload['is_active'] : $product->is_active,
                'sku' => $sku ?? $product->sku,
                'stock' => $payload['stock'] ?? $product->stock ?? 0,
                'reserved_stock' => $payload['reserved_stock'] ?? $product->reserved_stock ?? 0,
            ]);

            $product->update($productData);
            $this->syncCategories($product, $payload['category_ids'] ?? []);
            $optionLookup = $this->syncOptions($product, $payload['options'] ?? []);
            $variants = $this->syncVariants($product, $payload['variants'] ?? [], $optionLookup);
            $this->syncMedia($product, $payload['media'] ?? [], $variants, $optionLookup);
            $this->syncAttributes($product, $payload['attributes'] ?? [], $variants);
            $this->updateBackfilledFields($product, $variants);

            return $product->fresh();
        });

        return new ProductResource($product->load([
            'brand',
            'categories',
            'options.values',
            'variants.optionValues.option',
            'variants.inventoryLevels.location',
            'mediaAssets',
            'attributeValues.definition',
        ]));
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return response()->noContent();
    }

    private function prepareTranslationPayload(array $data, array $locales, string $defaultLocale, ?Product $product = null): array
    {
        $fields = ['name', 'slug', 'summary', 'description'];
        $payload = [];

        foreach ($fields as $field) {
            $translations = $this->collectTranslations($data[$field . '_translations'] ?? [], $locales);
            $payload["{$field}_translations"] = $translations;
            $fallback = $field === 'name' ? ($product?->name ?? '') : ($product?->description ?? '');
            $value = $this->fallbackTranslation($translations, $defaultLocale, $fallback);

            if ($field === 'slug') {
                $value = $this->ensureUniqueSlug($value, $product);
                $payload["{$field}_translations"][$defaultLocale] = $value;
            }

            $payload[$field] = $value;
        }

        return $payload;
    }

    private function collectTranslations(array $input, array $locales): array
    {
        $translations = [];
        foreach ($locales as $locale) {
            $translations[$locale] = trim((string) ($input[$locale] ?? ''));
        }
        return $translations;
    }

    private function fallbackTranslation(array $translations, string $defaultLocale, ?string $fallback = ''): ?string
    {
        if (!empty($translations[$defaultLocale])) {
            return $translations[$defaultLocale];
        }

        foreach ($translations as $value) {
            if ($value !== '') {
                return $value;
            }
        }

        return $fallback;
    }

    private function resolvePrimaryCategory(array $payload, ?Product $product = null): ?int
    {
        $categoryIds = $payload['category_ids'] ?? [];
        if (!empty($categoryIds)) {
            return (int) $categoryIds[0];
        }

        return $product?->category_id;
    }

    private function syncCategories(Product $product, array $categoryIds): void
    {
        if (empty($categoryIds)) {
            return;
        }

        $product->categories()->sync(array_values($categoryIds));
        if (!$product->category_id) {
            $product->update(['category_id' => (int) $categoryIds[0]]);
        }
    }

    private function syncOptions(Product $product, array $optionsPayload): array
    {
        if (empty($optionsPayload)) {
            return [];
        }

        $optionLookup = [];
        $processedOptionIds = [];

        foreach ($optionsPayload as $optionData) {
            $option = $this->findOrCreateOption($product, $optionData);
            if (!$option) {
                continue;
            }

            $processedOptionIds[] = $option->id;
            $option->update([
                'code' => $optionData['code'],
                'name_translations' => $optionData['name_translations'] ?? $option->name_translations,
                'position' => $optionData['position'] ?? $option->position,
            ]);

            $optionLookup[$option->code] = [
                'option' => $option,
                'values' => $this->syncOptionValues($option, $optionData['values'] ?? []),
            ];
        }

        $product->options()->whereNotIn('id', $processedOptionIds)->delete();

        return $optionLookup;
    }

    private function findOrCreateOption(Product $product, array $payload): ?ProductOption
    {
        if (!empty($payload['id'])) {
            return $product->options()->find($payload['id']);
        }

        if (!empty($payload['code'])) {
            return $product->options()->firstOrCreate(
                ['code' => $payload['code']],
                [
                    'name_translations' => $payload['name_translations'] ?? [],
                    'position' => $payload['position'] ?? 0,
                ]
            );
        }

        return null;
    }

    private function syncOptionValues(ProductOption $option, array $values): array
    {
        $valueLookup = [];
        $processedIds = [];

        foreach ($values as $valuePayload) {
            $value = $this->findOrCreateOptionValue($option, $valuePayload);
            if (!$value) {
                continue;
            }

            $processedIds[] = $value->id;
            $value->update([
                'label_translations' => $valuePayload['label_translations'] ?? $value->label_translations,
                'swatch_hex' => $valuePayload['swatch_hex'] ?? $value->swatch_hex,
                'position' => $valuePayload['position'] ?? $value->position,
            ]);

            $valueLookup[$value->value] = $value;
            $valueLookup[$value->id] = $value;
        }

        $option->values()->whereNotIn('id', $processedIds)->delete();

        return $valueLookup;
    }

    private function findOrCreateOptionValue(ProductOption $option, array $payload): ?ProductOptionValue
    {
        if (!empty($payload['id'])) {
            return $option->values()->find($payload['id']);
        }

        if (!empty($payload['value'])) {
            return $option->values()->firstOrCreate(
                ['value' => $payload['value']],
                [
                    'label_translations' => $payload['label_translations'] ?? [],
                    'swatch_hex' => $payload['swatch_hex'] ?? null,
                    'position' => $payload['position'] ?? 0,
                ]
            );
        }

        return null;
    }

    private function syncVariants(Product $product, array $variantPayloads, array $optionLookup): \Illuminate\Support\Collection
    {
        if (empty($variantPayloads)) {
            return collect();
        }

        $variantIds = [];
        $processedVariants = [];

        foreach ($variantPayloads as $variantPayload) {
            $variant = $this->findOrCreateVariant($product, $variantPayload);

            $variant->fill([
                'sku' => $variantPayload['sku'] ?? $variant->sku,
                'gtin' => array_key_exists('gtin', $variantPayload) ? $variantPayload['gtin'] : $variant->gtin,
                'mpn' => array_key_exists('mpn', $variantPayload) ? $variantPayload['mpn'] : $variant->mpn,
                'has_sensor' => array_key_exists('has_sensor', $variantPayload) ? (bool) $variantPayload['has_sensor'] : ($variant->has_sensor ?? false),
                'is_active' => array_key_exists('is_active', $variantPayload) ? (bool) $variantPayload['is_active'] : ($variant->is_active ?? true),
                'currency' => array_key_exists('currency', $variantPayload) ? $variantPayload['currency'] : $variant->currency,
                'price_cents' => array_key_exists('price_cents', $variantPayload) ? $variantPayload['price_cents'] : $variant->price_cents,
                'compare_at_cents' => array_key_exists('compare_at_cents', $variantPayload) ? $variantPayload['compare_at_cents'] : $variant->compare_at_cents,
                'cost_cents' => array_key_exists('cost_cents', $variantPayload) ? $variantPayload['cost_cents'] : $variant->cost_cents,
                'sale_cents' => array_key_exists('sale_cents', $variantPayload) ? $variantPayload['sale_cents'] : $variant->sale_cents,
                'sale_starts_at' => array_key_exists('sale_starts_at', $variantPayload) ? $variantPayload['sale_starts_at'] : $variant->sale_starts_at,
                'sale_ends_at' => array_key_exists('sale_ends_at', $variantPayload) ? $variantPayload['sale_ends_at'] : $variant->sale_ends_at,
                'weight_grams' => array_key_exists('weight_grams', $variantPayload) ? $variantPayload['weight_grams'] : $variant->weight_grams,
                'length_mm' => array_key_exists('length_mm', $variantPayload) ? $variantPayload['length_mm'] : $variant->length_mm,
                'width_mm' => array_key_exists('width_mm', $variantPayload) ? $variantPayload['width_mm'] : $variant->width_mm,
                'height_mm' => array_key_exists('height_mm', $variantPayload) ? $variantPayload['height_mm'] : $variant->height_mm,
                'track_inventory' => array_key_exists('track_inventory', $variantPayload) ? (bool) $variantPayload['track_inventory'] : ($variant->track_inventory ?? true),
                'allow_backorder' => array_key_exists('allow_backorder', $variantPayload) ? (bool) $variantPayload['allow_backorder'] : ($variant->allow_backorder ?? false),
                'low_stock_threshold' => array_key_exists('low_stock_threshold', $variantPayload) ? $variantPayload['low_stock_threshold'] : ($variant->low_stock_threshold ?? 0),
                'metadata' => array_key_exists('metadata', $variantPayload) ? $variantPayload['metadata'] : $variant->metadata,
            ]);

            $variant->save();
            $variantIds[] = $variant->id;
            $processedVariants[$variant->sku] = $variant;

            $optionValueIds = $this->resolveOptionValueIds($variantPayload['option_values'] ?? [], $optionLookup);
            $variant->optionValues()->sync($optionValueIds);
            $this->syncInventoryLevels($variant, $variantPayload['inventory'] ?? []);
        }

        $product->variants()->whereNotIn('id', $variantIds)->delete();

        return collect($processedVariants);
    }

    private function findOrCreateVariant(Product $product, array $payload): ProductVariant
    {
        if (!empty($payload['id'])) {
            return $product->variants()->findOrFail($payload['id']);
        }

        if (!empty($payload['sku'])) {
            return $product->variants()->firstOrNew(['sku' => $payload['sku']]);
        }

        return $product->variants()->make();
    }

    private function resolveOptionValueIds(array $optionValues, array $optionLookup): array
    {
        $resolved = [];

        foreach ($optionValues as $entry) {
            if (!empty($entry['option_value_id'])) {
                $resolved[] = $entry['option_value_id'];
                continue;
            }

            if (empty($entry['option_code']) || empty($entry['value'])) {
                continue;
            }

            $lookup = $optionLookup[$entry['option_code']]['values'] ?? [];
            if (isset($lookup[$entry['value']])) {
                $resolved[] = $lookup[$entry['value']]->id;
            }
        }

        return array_values(array_unique($resolved));
    }

    private function syncInventoryLevels(ProductVariant $variant, array $levels): void
    {
        if (empty($levels)) {
            return;
        }

        $processed = [];
        foreach ($levels as $level) {
            $location = InventoryLocation::where('code', $level['location_code'])->first();
            if (!$location) {
                continue;
            }

            $inventory = $variant->inventoryLevels()->updateOrCreate(
                ['location_id' => $location->id],
                [
                    'on_hand' => $level['on_hand'] ?? 0,
                    'reserved' => $level['reserved'] ?? 0,
                ]
            );

            $processed[] = $inventory->id;
        }

        $variant->inventoryLevels()->whereNotIn('id', $processed)->delete();
    }

    private function syncMedia(Product $product, array $assets, \Illuminate\Support\Collection $variants, array $optionLookup): void
    {
        if (empty($assets)) {
            return;
        }

        $product->mediaAssets()->delete();
        $variantMap = $variants->keyBy('sku');

        foreach ($assets as $index => $asset) {
            $variant = isset($asset['variant_sku']) ? $variantMap->get($asset['variant_sku']) : null;
            $optionValueId = $asset['option_value_id'] ?? null;

            if (!$optionValueId && !empty($asset['option_code']) && !empty($asset['value'])) {
                $lookup = $optionLookup[$asset['option_code']]['values'] ?? [];
                if (isset($lookup[$asset['value']])) {
                    $optionValueId = $lookup[$asset['value']]->id;
                }
            }

            MediaAsset::create([
                'product_id' => $product->id,
                'variant_id' => $variant?->id,
                'option_value_id' => $optionValueId,
                'url' => $asset['url'],
                'type' => strtolower($asset['type'] ?? 'image'),
                'alt_text' => $asset['alt_text'] ?? null,
                'position' => $asset['position'] ?? $index,
                'is_primary' => !empty($asset['is_primary']),
            ]);
        }
    }

    private function syncAttributes(Product $product, array $attributes, \Illuminate\Support\Collection $variants): void
    {
        if (empty($attributes)) {
            return;
        }

        $variantsBySku = $variants->keyBy('sku');
        $definitions = AttributeDefinition::all()->keyBy('key');
        $processed = [];

        foreach ($attributes as $attribute) {
            $definition = $definitions[$attribute['definition_key']] ?? null;
            if (!$definition) {
                continue;
            }

            $variant = null;
            if (!empty($attribute['variant_sku'])) {
                $variant = $variantsBySku->get($attribute['variant_sku']);
            }

            $payload = [
                'definition_id' => $definition->id,
                'product_id' => $variant ? null : $product->id,
                'variant_id' => $variant?->id,
            ];

            $valuePayload = $this->attributeValuePayload($definition, $attribute['value'] ?? null);
            if ($valuePayload === null) {
                continue;
            }

            $payload = array_merge($payload, $valuePayload);

            $value = AttributeValue::updateOrCreate(
                [
                    'definition_id' => $payload['definition_id'],
                    'product_id' => $payload['product_id'],
                    'variant_id' => $payload['variant_id'],
                ],
                $payload
            );

            $processed[] = $value->id;
        }

        $product->attributeValues()->whereNotIn('id', $processed)->delete();
    }

    private function attributeValuePayload(AttributeDefinition $definition, $value): ?array
    {
        if ($value === null) {
            return null;
        }

        $payload = [];

        switch ($definition->type) {
            case 'NUMBER':
                if (!is_numeric($value)) {
                    return null;
                }
                $payload['number_value'] = $value;
                break;
            case 'BOOLEAN':
                $payload['boolean_value'] = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                break;
            case 'MULTISELECT':
            case 'DIMENSIONS':
            case 'WEIGHT':
                $payload['json_value'] = is_array($value) ? $value : json_decode((string) $value, true);
                break;
            default:
                $payload['text_value'] = (string) $value;
        }

        return $payload;
    }

    private function ensureUniqueSlug(string $slug, ?Product $product = null): string
    {
        $base = Str::slug($slug) ?: 'product';
        $candidate = $base;
        $counter = 1;

        while (
            Product::where('slug', $candidate)
                ->when($product, fn ($query) => $query->where('id', '<>', $product->id))
                ->exists()
        ) {
            $candidate = "{$base}-{$counter}";
            $counter++;
        }

        return $candidate;
    }

    private function updateBackfilledFields(Product $product, \Illuminate\Support\Collection $variants): void
    {
        $firstVariant = $variants->first();
        if (!$firstVariant) {
            return;
        }

        $mediaUrls = $product->mediaAssets()->orderBy('position')->pluck('url')->all();
        $primary = $product->mediaAssets()->where('is_primary', true)->first();

        $galleryUrls = $mediaUrls ? array_slice($mediaUrls, 1) : [];

        $product->update([
            'sku' => $firstVariant->sku,
            'price' => $firstVariant->price_cents / 100,
            'compare_at_price' => $firstVariant->compare_at_cents ? $firstVariant->compare_at_cents / 100 : null,
            'weight_grams' => $firstVariant->weight_grams,
            'image' => $primary?->url,
            'thumbnail' => $primary?->url,
            'gallery' => count($galleryUrls) ? json_encode($galleryUrls) : null,
            'images' => $mediaUrls ? json_encode($mediaUrls) : null,
        ]);
    }
}
