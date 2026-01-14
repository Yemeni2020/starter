<?php

namespace App\Http\Controllers\Storefront;

use App\Http\Controllers\Controller;
use App\Models\Product;

class ProductPageController extends Controller
{
    public function show(string $slug)
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
            ->active()
            ->where(function ($query) use ($slug, $locale) {
                $query->where('slug', $slug)
                    ->orWhereRaw("json_extract(slug_translations, '$.\"{$locale}\"') = ?", [$slug]);
            })
            ->firstOrFail();

        return view('store.product', [
            'productPayload' => $this->serializeProduct($product),
        ]);
    }

    private function serializeProduct(Product $product): array
    {
        $locale = app()->getLocale();
        $variants = $product->variants->map(function ($variant) use ($locale) {
            return [
                'id' => $variant->id,
                'sku' => $variant->sku,
                'price_cents' => $variant->effective_price_cents,
                'compare_at_cents' => $variant->compare_at_cents,
                'currency' => $variant->currency,
                'available_quantity' => $variant->available_quantity,
                'option_value_ids' => $variant->optionValues->pluck('id')->all(),
                'option_labels' => $variant->optionValues->map(fn ($value) => $value->label($locale))->all(),
            ];
        })->values();

        $options = $product->options->map(function ($option) use ($locale) {
            return [
                'code' => $option->code,
                'name' => $option->label($locale),
                'values' => $option->values->map(function ($value) use ($locale) {
                    return [
                        'id' => $value->id,
                        'label' => $value->label($locale),
                        'value' => $value->value,
                        'swatch_hex' => $value->swatch_hex,
                    ];
                })->values(),
            ];
        })->values();

        $media = $product->mediaAssets->map(function ($asset) {
            return [
                'id' => $asset->id,
                'url' => $asset->url,
                'variant_id' => $asset->variant_id,
                'option_value_id' => $asset->option_value_id,
                'is_primary' => $asset->is_primary,
            ];
        })->values();

        $attributes = $product->attributeValues->map(function ($value) use ($locale) {
            $attributeValue = $value->value();
            $display = is_array($attributeValue) ? implode(', ', $attributeValue) : $attributeValue;

            return [
                'label' => $value->definition->label($locale),
                'value' => $display,
                'type' => $value->definition->type,
                'variant_id' => $value->variant_id,
            ];
        })->values();

        return [
            'id' => $product->id,
            'name' => $product->name,
            'brand' => $product->brand?->name,
            'summary' => $product->summary,
            'description' => $product->description,
            'category' => $product->category?->name,
            'currency' => $variants->first()?->get('currency') ?? 'USD',
            'options' => $options,
            'variants' => $variants,
            'media' => $media,
            'attributes' => $attributes,
            'primary_media_url' => $product->mediaAssets->firstWhere('is_primary', true)?->url ?? $product->image,
            'price_cents' => $variants->first() ? $variants->first()['price_cents'] : null,
        ];
    }
}
