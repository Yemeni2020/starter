<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'code',
        'name_translations',
        'position',
    ];

    protected $casts = [
        'name_translations' => 'array',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function values()
    {
        return $this->hasMany(ProductOptionValue::class);
    }

    public function label(string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return $this->name_translations[$locale] ?? $this->name_translations['en'] ?? $this->code;
    }
}
