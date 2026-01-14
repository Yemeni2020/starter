<?php

namespace App\Models;

use App\Models\InventoryLevel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductVariant extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'sku',
        'gtin',
        'mpn',
        'has_sensor',
        'is_active',
        'currency',
        'price_cents',
        'compare_at_cents',
        'cost_cents',
        'sale_cents',
        'sale_starts_at',
        'sale_ends_at',
        'weight_grams',
        'length_mm',
        'width_mm',
        'height_mm',
        'track_inventory',
        'allow_backorder',
        'low_stock_threshold',
        'metadata',
    ];

    protected $casts = [
        'has_sensor' => 'boolean',
        'is_active' => 'boolean',
        'price_cents' => 'integer',
        'compare_at_cents' => 'integer',
        'cost_cents' => 'integer',
        'sale_cents' => 'integer',
        'sale_starts_at' => 'datetime',
        'sale_ends_at' => 'datetime',
        'weight_grams' => 'integer',
        'length_mm' => 'integer',
        'width_mm' => 'integer',
        'height_mm' => 'integer',
        'track_inventory' => 'boolean',
        'allow_backorder' => 'boolean',
        'low_stock_threshold' => 'integer',
        'metadata' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function optionValues()
    {
        return $this->belongsToMany(
            ProductOptionValue::class,
            'product_variant_option_value',
            'variant_id',
            'option_value_id'
        );
    }

    public function inventoryLevels()
    {
        return $this->hasMany(InventoryLevel::class, 'variant_id');
    }

    public function mediaAssets()
    {
        return $this->hasMany(MediaAsset::class, 'variant_id');
    }

    public function attributeValues()
    {
        return $this->hasMany(AttributeValue::class, 'variant_id');
    }

    public function getComputedTitleAttribute(): string
    {
        $locale = app()->getLocale();

        $labels = $this->optionValues
            ->sortBy(fn (ProductOptionValue $value) => $value->option?->position ?? 0)
            ->map(fn (ProductOptionValue $value) => $value->label($locale))
            ->filter()
            ->values();

        return $labels->isEmpty() ? $this->sku : $labels->implode(' / ');
    }

    public function getEffectivePriceCentsAttribute(): int
    {
        $now = now();

        if (
            $this->sale_cents &&
            $this->sale_starts_at &&
            $this->sale_ends_at &&
            $now->between($this->sale_starts_at, $this->sale_ends_at)
        ) {
            return $this->sale_cents;
        }

        return $this->price_cents;
    }

    public function getAvailableQuantityAttribute(): int
    {
        if (!$this->track_inventory) {
            return 999999;
        }

        return $this->inventoryLevels->sum(function (InventoryLevel $level) {
            return max(0, $level->on_hand - $level->reserved);
        });
    }
}
