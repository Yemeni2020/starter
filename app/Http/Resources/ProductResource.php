<?php

namespace App\Http\Resources;

use App\Models\AttributeValue;
use App\Models\InventoryLevel;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'summary' => $this->summary,
            'description' => $this->description,
            'price' => $this->price,
            'compare_at_price' => $this->compare_at_price,
            'sku' => $this->sku,
            'stock' => $this->stock,
            'reserved_stock' => $this->reserved_stock,
            'available_stock' => $this->availableStock(),
            'is_active' => $this->is_active,
            'weight_grams' => $this->weight_grams,
            'brand' => $this->whenLoaded('brand', function () {
                return [
                    'id' => $this->brand->id,
                    'name' => $this->brand->name,
                    'slug' => $this->brand->slug,
                ];
            }),
            'categories' => $this->whenLoaded('categories', function () {
                return $this->categories->map(function ($category) {
                    return [
                        'id' => $category->id,
                        'name' => $category->name,
                        'slug' => $category->slug,
                    ];
                })->values();
            }),
            'options' => $this->whenLoaded('options', function () {
                return $this->options->map(function ($option) {
                    return [
                        'id' => $option->id,
                        'code' => $option->code,
                        'name_translations' => $option->name_translations,
                        'position' => $option->position,
                        'values' => $option->values->map(function ($value) {
                            return [
                                'id' => $value->id,
                                'value' => $value->value,
                                'swatch_hex' => $value->swatch_hex,
                                'label_translations' => $value->label_translations,
                                'position' => $value->position,
                            ];
                        })->values(),
                    ];
                })->values();
            }),
            'variants' => $this->whenLoaded('variants', function () {
                return $this->variants->map(function (ProductVariant $variant) {
                    return [
                        'id' => $variant->id,
                        'sku' => $variant->sku,
                        'gtin' => $variant->gtin,
                        'mpn' => $variant->mpn,
                        'has_sensor' => $variant->has_sensor,
                        'is_active' => $variant->is_active,
                        'currency' => $variant->currency,
                        'price_cents' => $variant->price_cents,
                        'compare_at_cents' => $variant->compare_at_cents,
                        'cost_cents' => $variant->cost_cents,
                        'sale_cents' => $variant->sale_cents,
                        'sale_starts_at' => $variant->sale_starts_at,
                        'sale_ends_at' => $variant->sale_ends_at,
                        'weight_grams' => $variant->weight_grams,
                        'length_mm' => $variant->length_mm,
                        'width_mm' => $variant->width_mm,
                        'height_mm' => $variant->height_mm,
                        'track_inventory' => $variant->track_inventory,
                        'allow_backorder' => $variant->allow_backorder,
                        'low_stock_threshold' => $variant->low_stock_threshold,
                        'metadata' => $variant->metadata,
                        'computed_title' => $variant->computed_title,
                        'effective_price_cents' => $variant->effective_price_cents,
                        'available_quantity' => $variant->available_quantity,
                        'option_values' => $variant->optionValues->map(function ($optionValue) {
                            return [
                                'id' => $optionValue->id,
                                'value' => $optionValue->value,
                                'label_translations' => $optionValue->label_translations,
                                'swatch_hex' => $optionValue->swatch_hex,
                                'product_option_id' => $optionValue->product_option_id,
                            ];
                        })->values(),
                        'inventory' => $variant->inventoryLevels->map(function (InventoryLevel $level) {
                            return [
                                'location_id' => $level->location->id,
                                'location_code' => $level->location->code,
                                'location_name' => $level->location->name,
                                'on_hand' => $level->on_hand,
                                'reserved' => $level->reserved,
                                'available' => $level->available,
                            ];
                        })->values(),
                    ];
                })->values();
            }),
            'media' => $this->whenLoaded('mediaAssets', function () {
                return $this->mediaAssets->map(function ($asset) {
                    return [
                        'id' => $asset->id,
                        'url' => $asset->url,
                        'type' => $asset->type,
                        'alt_text' => $asset->alt_text,
                        'position' => $asset->position,
                        'is_primary' => $asset->is_primary,
                        'option_value_id' => $asset->option_value_id,
                        'variant_id' => $asset->variant_id,
                    ];
                })->values();
            }),
            'attributes' => $this->whenLoaded('attributeValues', function () {
                return $this->attributeValues->map(function (AttributeValue $value) {
                    return [
                        'id' => $value->id,
                        'definition_key' => $value->definition->key,
                        'label' => $value->definition->label(),
                        'type' => $value->definition->type,
                        'variant_id' => $value->variant_id,
                        'value' => $value->value(),
                    ];
                })->values();
            }),
        ];
    }
}
