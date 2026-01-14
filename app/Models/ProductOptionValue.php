<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOptionValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_option_id',
        'label_translations',
        'value',
        'swatch_hex',
        'position',
    ];

    protected $casts = [
        'label_translations' => 'array',
    ];

    public function option()
    {
        return $this->belongsTo(ProductOption::class, 'product_option_id');
    }

    public function mediaAssets()
    {
        return $this->hasMany(MediaAsset::class);
    }

    public function variants()
    {
        return $this->belongsToMany(
            ProductVariant::class,
            'product_variant_option_value',
            'option_value_id',
            'variant_id'
        );
    }

    public function label(string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return $this->label_translations[$locale] ?? $this->label_translations['en'] ?? $this->value;
    }
}
