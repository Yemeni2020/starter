<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Category extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $fillable = [
        'name',
        'slug',
        'name_translations',
        'slug_translations',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'name_translations' => 'array',
        'slug_translations' => 'array',
    ];

    protected $translatable = [
        'name_translations',
        'slug_translations',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getNameAttribute($value): ?string
    {
        $translation = $this->getTranslation('name_translations', app()->getLocale(), false);

        return $translation ?? $value;
    }

    public function getSlugAttribute($value): ?string
    {
        $translation = $this->getTranslation('slug_translations', app()->getLocale(), false);

        return $translation ?? $value;
    }
}
