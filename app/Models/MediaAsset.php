<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MediaAsset extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'option_value_id',
        'variant_id',
        'url',
        'type',
        'alt_text',
        'position',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function optionValue()
    {
        return $this->belongsTo(ProductOptionValue::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
}
