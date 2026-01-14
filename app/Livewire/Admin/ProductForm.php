<?php

namespace App\Livewire\Admin;

use App\Models\AttributeDefinition;
use App\Models\AttributeValue;
use App\Models\Category;
use App\Models\InventoryLocation;
use App\Models\MediaAsset;
use App\Models\Product;
use App\Models\ProductOption;
use App\Models\ProductOptionValue;
use App\Models\ProductVariant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Livewire\Component;

class ProductForm extends Component
{
    public ?Product $product = null;

    public array $categories = [];
    public array $attributeDefinitions = [];

    public array $name_translations = ['ar' => '', 'en' => ''];
    public array $slug_translations = ['ar' => '', 'en' => ''];
    public array $summary_translations = ['ar' => '', 'en' => ''];
    public array $description_translations = ['ar' => '', 'en' => ''];

    public ?int $category_id = null;
    public string $status = 'ACTIVE';
    public bool $is_active = true;
    public float $price = 0;
    public ?float $compare_at_price = null;
    public string $sku = '';
    public int $stock = 0;
    public int $reserved_stock = 0;
    public int $weight_grams = 0;

    public array $options = [];
    public array $variants = [];
    public array $media = [];
    public array $productAttributes = [];

    public string $activeTab = 'basic';

    public function mount(?Product $product = null): void
    {
        $this->categories = Category::orderBy('name')->get()
            ->map(fn (Category $category) => ['id' => $category->id, 'name' => $category->name])
            ->all();

        $this->attributeDefinitions = AttributeDefinition::orderBy('sort_order')->get()
            ->map(fn (AttributeDefinition $definition) => [
                'key' => $definition->key,
                'label' => $definition->label(),
                'type' => $definition->type,
                'options' => $definition->options ?? [],
                'is_variant_specific' => $definition->is_variant_specific,
            ])->all();

        if ($product) {
            $product->load([
                'options.values',
                'variants.optionValues.option',
                'variants.inventoryLevels.location',
                'mediaAssets',
                'attributeValues.definition',
            ]);

            $this->product = $product;
            $this->fillProductState($product);
        } else {
            $this->options = [
                ['code' => 'color', 'name_translations' => ['ar' => 'اللون', 'en' => 'Color'], 'position' => 1, 'values' => []],
                ['code' => 'size', 'name_translations' => ['ar' => 'المقاس', 'en' => 'Size'], 'position' => 2, 'values' => []],
            ];
        }
    }

    public function setTab(string $tab): void
    {
        $this->activeTab = $tab;
    }

    public function addOption(): void
    {
        $this->options[] = [
            'code' => '',
            'name_translations' => ['ar' => '', 'en' => ''],
            'position' => count($this->options) + 1,
            'values' => [],
        ];
    }

    public function removeOption(int $index): void
    {
        array_splice($this->options, $index, 1);
    }

    public function addOptionValue(int $optionIndex): void
    {
        $this->options[$optionIndex]['values'][] = [
            'value' => '',
            'label_translations' => ['ar' => '', 'en' => ''],
            'swatch_hex' => '',
            'position' => count($this->options[$optionIndex]['values']) + 1,
        ];
    }

    public function removeOptionValue(int $optionIndex, int $valueIndex): void
    {
        array_splice($this->options[$optionIndex]['values'], $valueIndex, 1);
    }

    public function addVariant(): void
    {
        $this->variants[] = $this->blankVariant();
    }

    public function removeVariant(int $index): void
    {
        array_splice($this->variants, $index, 1);
    }

    public function generateVariants(): void
    {
        $color = $this->findOption('color');
        $size = $this->findOption('size');

        if (!$color || !$size) {
            return;
        }

        $base = Str::upper(Str::slug($this->name_translations['en'] ?: $this->name_translations['ar'] ?: 'product', '_'));
        $priceCents = (int) round($this->price * 100);

        $variants = [];

        foreach ($color['values'] as $colorValue) {
            foreach ($size['values'] as $sizeValue) {
                $sku = strtoupper($base . '-' . ($colorValue['value'] ?: 'COLOR') . '-' . ($sizeValue['value'] ?: 'SIZE'));
                $variants[] = array_merge($this->blankVariant(), [
                    'sku' => $sku,
                    'price_cents' => $priceCents,
                    'option_values' => [
                        'color' => $colorValue['value'],
                        'size' => $sizeValue['value'],
                    ],
                ]);
            }
        }

        $this->variants = $variants;
    }

    public function addMedia(): void
    {
        $this->media[] = [
            'url' => '',
            'type' => 'image',
            'alt_text' => '',
            'position' => count($this->media),
            'is_primary' => false,
            'option_value_id' => null,
            'variant_sku' => null,
        ];
    }

    public function removeMedia(int $index): void
    {
        array_splice($this->media, $index, 1);
    }

    public function addAttribute(): void
    {
        $this->productAttributes[] = [
            'definition_key' => '',
            'value' => '',
            'variant_sku' => null,
        ];
    }

    public function removeAttribute(int $index): void
    {
        array_splice($this->productAttributes, $index, 1);
    }

    public function save(): void
    {
        $this->validate($this->rules());

        $locales = config('app.supported_locales', ['ar', 'en']);
        $defaultLocale = config('app.locale', 'ar');

        DB::transaction(function () use ($locales, $defaultLocale) {
            $nameTranslations = $this->normalizeTranslations($this->name_translations, $locales);
            $slugTranslations = $this->normalizeTranslations($this->slug_translations, $locales);
            $summaryTranslations = $this->normalizeTranslations($this->summary_translations, $locales);
            $descriptionTranslations = $this->normalizeTranslations($this->description_translations, $locales);

            if (empty($slugTranslations[$defaultLocale])) {
                $slugTranslations[$defaultLocale] = Str::slug($nameTranslations[$defaultLocale] ?: 'product');
            }

            $slugTranslations[$defaultLocale] = $this->ensureUniqueSlug($slugTranslations[$defaultLocale], $this->product);

            $productData = [
                'category_id' => $this->category_id,
                'name_translations' => $nameTranslations,
                'slug_translations' => $slugTranslations,
                'summary_translations' => $summaryTranslations,
                'description_translations' => $descriptionTranslations,
                'name' => $nameTranslations[$defaultLocale] ?: ($nameTranslations['en'] ?? ''),
                'slug' => $slugTranslations[$defaultLocale],
                'summary' => $summaryTranslations[$defaultLocale] ?: null,
                'description' => $descriptionTranslations[$defaultLocale] ?: null,
                'price' => $this->price,
                'compare_at_price' => $this->compare_at_price,
                'sku' => $this->sku ?: ($this->variants[0]['sku'] ?? Str::upper(Str::random(8))),
                'stock' => $this->stock,
                'reserved_stock' => $this->reserved_stock,
                'weight_grams' => $this->weight_grams,
                'status' => $this->status ?: 'ACTIVE',
                'is_active' => $this->is_active,
            ];

            if ($this->product) {
                $this->product->update($productData);
            } else {
                $this->product = Product::create($productData);
            }

            if ($this->category_id) {
                $this->product->categories()->sync([$this->category_id]);
            }

            $optionLookup = $this->syncOptions($this->product, $this->options);
            $variants = $this->syncVariants($this->product, $this->variants, $optionLookup);
            $this->syncMedia($this->product, $this->media, $variants, $optionLookup);
            $this->syncAttributes($this->product, $this->productAttributes, $variants);

            $this->updateBackfilledFields($this->product, $variants);
        });

        $this->redirectRoute('dashboard.products.edit', ['product' => $this->product->id]);
    }

    private function rules(): array
    {
        $defaultLocale = config('app.locale', 'ar');

        return [
            "name_translations.{$defaultLocale}" => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'sku' => ['nullable', 'string', 'max:255'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'reserved_stock' => ['nullable', 'integer', 'min:0'],
            'weight_grams' => ['nullable', 'integer', 'min:0'],
            'options' => ['array'],
            'options.*.code' => ['required', 'string', 'max:64'],
            'options.*.values' => ['array'],
            'options.*.values.*.value' => ['required', 'string', 'max:255'],
            'variants' => ['array'],
            'variants.*.sku' => ['required', 'string', 'max:255'],
            'variants.*.price_cents' => ['required', 'integer', 'min:0'],
        ];
    }

    private function fillProductState(Product $product): void
    {
        $this->name_translations = $product->name_translations ?? $this->name_translations;
        $this->slug_translations = $product->slug_translations ?? $this->slug_translations;
        $this->summary_translations = $product->summary_translations ?? $this->summary_translations;
        $this->description_translations = $product->description_translations ?? $this->description_translations;
        $this->category_id = $product->category_id;
        $this->status = $product->status ?? 'ACTIVE';
        $this->is_active = (bool) $product->is_active;
        $this->price = (float) $product->price;
        $this->compare_at_price = $product->compare_at_price ? (float) $product->compare_at_price : null;
        $this->sku = $product->sku ?? '';
        $this->stock = (int) ($product->stock ?? 0);
        $this->reserved_stock = (int) ($product->reserved_stock ?? 0);
        $this->weight_grams = (int) ($product->weight_grams ?? 0);

        $this->options = $product->options->sortBy('position')->map(function (ProductOption $option) {
            return [
                'id' => $option->id,
                'code' => $option->code,
                'name_translations' => $option->name_translations ?? ['ar' => '', 'en' => ''],
                'position' => $option->position,
                'values' => $option->values->sortBy('position')->map(function (ProductOptionValue $value) {
                    return [
                        'id' => $value->id,
                        'value' => $value->value,
                        'label_translations' => $value->label_translations ?? ['ar' => '', 'en' => ''],
                        'swatch_hex' => $value->swatch_hex,
                        'position' => $value->position,
                    ];
                })->values()->all(),
            ];
        })->values()->all();

        $this->variants = $product->variants->map(function (ProductVariant $variant) {
            $inventory = $variant->inventoryLevels->firstWhere('location.code', 'MAIN');
            return [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'price_cents' => $variant->price_cents,
                'compare_at_cents' => $variant->compare_at_cents,
                'cost_cents' => $variant->cost_cents,
                'sale_cents' => $variant->sale_cents,
                'sale_starts_at' => optional($variant->sale_starts_at)->format('Y-m-d'),
                'sale_ends_at' => optional($variant->sale_ends_at)->format('Y-m-d'),
                'has_sensor' => (bool) $variant->has_sensor,
                'is_active' => (bool) $variant->is_active,
                'weight_grams' => $variant->weight_grams,
                'length_mm' => $variant->length_mm,
                'width_mm' => $variant->width_mm,
                'height_mm' => $variant->height_mm,
                'track_inventory' => (bool) $variant->track_inventory,
                'allow_backorder' => (bool) $variant->allow_backorder,
                'low_stock_threshold' => $variant->low_stock_threshold,
                'option_values' => $variant->optionValues
                    ->filter(fn (ProductOptionValue $value) => !empty($value->option?->code))
                    ->mapWithKeys(fn (ProductOptionValue $value) => [$value->option->code => $value->value])
                    ->all(),
                'inventory' => [
                    'on_hand' => $inventory?->on_hand ?? 0,
                    'reserved' => $inventory?->reserved ?? 0,
                ],
            ];
        })->values()->all();

        $this->media = $product->mediaAssets->sortBy('position')->map(function (MediaAsset $asset) {
            return [
                'id' => $asset->id,
                'url' => $asset->url,
                'type' => $asset->type,
                'alt_text' => $asset->alt_text,
                'position' => $asset->position,
                'is_primary' => (bool) $asset->is_primary,
                'option_value_id' => $asset->option_value_id,
                'variant_sku' => $asset->variant?->sku,
            ];
        })->values()->all();

        $this->productAttributes = $product->attributeValues->map(function (AttributeValue $value) {
            return [
                'definition_key' => $value->definition->key,
                'value' => $value->value(),
                'variant_sku' => $value->variant?->sku,
            ];
        })->values()->all();
    }

    private function normalizeTranslations(array $input, array $locales): array
    {
        $translations = [];
        foreach ($locales as $locale) {
            $translations[$locale] = trim((string) ($input[$locale] ?? ''));
        }
        return $translations;
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

    private function findOption(string $code): ?array
    {
        foreach ($this->options as $option) {
            if (($option['code'] ?? '') === $code) {
                return $option;
            }
        }
        return null;
    }

    private function blankVariant(): array
    {
        return [
            'sku' => '',
            'price_cents' => 0,
            'compare_at_cents' => null,
            'cost_cents' => null,
            'sale_cents' => null,
            'sale_starts_at' => null,
            'sale_ends_at' => null,
            'has_sensor' => false,
            'is_active' => true,
            'weight_grams' => null,
            'length_mm' => null,
            'width_mm' => null,
            'height_mm' => null,
            'track_inventory' => true,
            'allow_backorder' => false,
            'low_stock_threshold' => 0,
            'option_values' => [],
            'inventory' => ['on_hand' => 0, 'reserved' => 0],
        ];
    }

    private function syncOptions(Product $product, array $optionsPayload): array
    {
        $optionLookup = [];
        $processedOptionIds = [];

        foreach ($optionsPayload as $optionData) {
            $code = trim((string) ($optionData['code'] ?? ''));
            if ($code === '') {
                continue;
            }

            $option = !empty($optionData['id'])
                ? $product->options()->find($optionData['id'])
                : $product->options()->firstOrCreate(['code' => $code], [
                    'name_translations' => $optionData['name_translations'] ?? [],
                    'position' => $optionData['position'] ?? 0,
                ]);

            if (!$option) {
                continue;
            }

            $option->update([
                'code' => $code,
                'name_translations' => $optionData['name_translations'] ?? $option->name_translations,
                'position' => $optionData['position'] ?? $option->position,
            ]);

            $processedOptionIds[] = $option->id;
            $optionLookup[$code] = [
                'option' => $option,
                'values' => $this->syncOptionValues($option, $optionData['values'] ?? []),
            ];
        }

        $product->options()->whereNotIn('id', $processedOptionIds)->delete();

        return $optionLookup;
    }

    private function syncOptionValues(ProductOption $option, array $values): array
    {
        $valueLookup = [];
        $processedIds = [];

        foreach ($values as $valuePayload) {
            $value = trim((string) ($valuePayload['value'] ?? ''));
            if ($value === '') {
                continue;
            }

            $model = !empty($valuePayload['id'])
                ? $option->values()->find($valuePayload['id'])
                : $option->values()->firstOrCreate(['value' => $value], [
                    'label_translations' => $valuePayload['label_translations'] ?? [],
                    'swatch_hex' => $valuePayload['swatch_hex'] ?? null,
                    'position' => $valuePayload['position'] ?? 0,
                ]);

            if (!$model) {
                continue;
            }

            $model->update([
                'label_translations' => $valuePayload['label_translations'] ?? $model->label_translations,
                'swatch_hex' => $valuePayload['swatch_hex'] ?? $model->swatch_hex,
                'position' => $valuePayload['position'] ?? $model->position,
            ]);

            $processedIds[] = $model->id;
            $valueLookup[$value] = $model;
            $valueLookup[$model->id] = $model;
        }

        $option->values()->whereNotIn('id', $processedIds)->delete();

        return $valueLookup;
    }

    private function syncVariants(Product $product, array $variantPayloads, array $optionLookup)
    {
        $variantIds = [];
        $processedVariants = [];

        foreach ($variantPayloads as $variantPayload) {
            $variant = !empty($variantPayload['id'])
                ? $product->variants()->find($variantPayload['id'])
                : $product->variants()->firstOrNew(['sku' => $variantPayload['sku'] ?? Str::upper(Str::random(6))]);

            $variant->fill([
                'sku' => $variantPayload['sku'] ?? $variant->sku,
                'gtin' => $variantPayload['gtin'] ?? $variant->gtin,
                'mpn' => $variantPayload['mpn'] ?? $variant->mpn,
                'has_sensor' => (bool) ($variantPayload['has_sensor'] ?? false),
                'is_active' => (bool) ($variantPayload['is_active'] ?? true),
                'currency' => $variantPayload['currency'] ?? $variant->currency ?? 'USD',
                'price_cents' => (int) ($variantPayload['price_cents'] ?? 0),
                'compare_at_cents' => $variantPayload['compare_at_cents'] ?? null,
                'cost_cents' => $variantPayload['cost_cents'] ?? null,
                'sale_cents' => $variantPayload['sale_cents'] ?? null,
                'sale_starts_at' => $variantPayload['sale_starts_at'] ?? null,
                'sale_ends_at' => $variantPayload['sale_ends_at'] ?? null,
                'weight_grams' => $variantPayload['weight_grams'] ?? null,
                'length_mm' => $variantPayload['length_mm'] ?? null,
                'width_mm' => $variantPayload['width_mm'] ?? null,
                'height_mm' => $variantPayload['height_mm'] ?? null,
                'track_inventory' => (bool) ($variantPayload['track_inventory'] ?? true),
                'allow_backorder' => (bool) ($variantPayload['allow_backorder'] ?? false),
                'low_stock_threshold' => (int) ($variantPayload['low_stock_threshold'] ?? 0),
                'metadata' => $variantPayload['metadata'] ?? null,
            ]);

            $variant->product_id = $product->id;
            $variant->save();

            $variantIds[] = $variant->id;
            $processedVariants[$variant->sku] = $variant;

            $optionValueIds = $this->resolveOptionValueIds($variantPayload['option_values'] ?? [], $optionLookup);
            $variant->optionValues()->sync($optionValueIds);

            $inventory = $variantPayload['inventory'] ?? ['on_hand' => 0, 'reserved' => 0];
            $this->syncInventory($variant, (int) ($inventory['on_hand'] ?? 0), (int) ($inventory['reserved'] ?? 0));
        }

        $product->variants()->whereNotIn('id', $variantIds)->delete();

        return collect($processedVariants);
    }

    private function resolveOptionValueIds(array $optionValues, array $optionLookup): array
    {
        $resolved = [];

        $normalized = [];
        foreach ($optionValues as $key => $entry) {
            if (is_string($entry)) {
                $normalized[] = ['option_code' => $key, 'value' => $entry];
                continue;
            }
            $normalized[] = $entry;
        }

        foreach ($normalized as $entry) {
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

    private function syncInventory(ProductVariant $variant, int $onHand, int $reserved): void
    {
        $location = InventoryLocation::firstOrCreate(
            ['code' => 'MAIN'],
            ['name' => 'Main warehouse', 'address' => 'Primary fulfillment center']
        );

        $variant->inventoryLevels()->updateOrCreate(
            ['location_id' => $location->id],
            ['on_hand' => $onHand, 'reserved' => $reserved]
        );
    }

    private function syncMedia(Product $product, array $media, $variants, array $optionLookup): void
    {
        $product->mediaAssets()->delete();
        $variantMap = $variants->keyBy('sku');

        foreach ($media as $index => $asset) {
            $variant = !empty($asset['variant_sku']) ? $variantMap->get($asset['variant_sku']) : null;
            $optionValueId = $asset['option_value_id'] ?? null;

            if (!$optionValueId && !empty($asset['option_code']) && !empty($asset['value'])) {
                $lookup = $optionLookup[$asset['option_code']]['values'] ?? [];
                if (isset($lookup[$asset['value']])) {
                    $optionValueId = $lookup[$asset['value']]->id;
                }
            }

            if (empty($asset['url'])) {
                continue;
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

    private function syncAttributes(Product $product, array $attributes, $variants): void
    {
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
        if ($value === null || $value === '') {
            return null;
        }

        switch ($definition->type) {
            case 'NUMBER':
                return ['number_value' => (float) $value];
            case 'BOOLEAN':
                return ['boolean_value' => (bool) $value];
            case 'MULTISELECT':
            case 'DIMENSIONS':
            case 'WEIGHT':
                return ['json_value' => is_array($value) ? $value : json_decode((string) $value, true)];
            default:
                return ['text_value' => (string) $value];
        }
    }

    private function updateBackfilledFields(Product $product, $variants): void
    {
        $firstVariant = $variants->first();
        if ($firstVariant) {
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

    public function render()
    {
        return view('livewire.admin.product-form')->layout('admin.layouts.app', [
            'title' => $this->product ? 'Edit Product' : 'Create Product',
        ]);
    }
}
