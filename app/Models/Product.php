<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use Spatie\Translatable\HasTranslations;

class Product extends Model
{
    use HasFactory;
    use HasTranslations;

    protected $fillable = [
        'category_id',
        'brand_id',
        'name',
        'slug',
        'summary',
        'description',
        'features',
        'shipping_returns',
        'price',
        'compare_at_price',
        'rating',
        'reviews_count',
        'sku',
        'color',
        'stock',
        'reserved_stock',
        'is_active',
        'weight_grams',
        'status',
        'image',
        'thumbnail',
        'gallery',
        'images',
        'colors',
        'color_image',
        'name_translations',
        'slug_translations',
        'summary_translations',
        'description_translations',
        'seo_title_translations',
        'seo_description_translations',
        'seo_keywords_translations',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
        'rating' => 'decimal:1',
        'reviews_count' => 'integer',
        'is_active' => 'boolean',
        'gallery' => 'array',
        'features' => 'array',
        'shipping_returns' => 'array',
        'images' => 'array',
        'colors' => 'array',
        'name_translations' => 'array',
        'slug_translations' => 'array',
        'summary_translations' => 'array',
        'description_translations' => 'array',
        'seo_title_translations' => 'array',
        'seo_description_translations' => 'array',
        'seo_keywords_translations' => 'array',
    ];

    protected $translatable = [
        'name_translations',
        'slug_translations',
        'summary_translations',
        'description_translations',
        'seo_title_translations',
        'seo_description_translations',
        'seo_keywords_translations',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_product');
    }

    public function options(): HasMany
    {
        return $this->hasMany(ProductOption::class);
    }

    public function variants(): HasMany
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function mediaAssets(): HasMany
    {
        return $this->hasMany(MediaAsset::class)->orderBy('position');
    }

    public function media(): HasMany
    {
        return $this->mediaAssets();
    }

    public function attributeValues(): HasMany
    {
        return $this->hasMany(AttributeValue::class);
    }

    public function colorOptions(): BelongsToMany
    {
        return $this->belongsToMany(Color::class, 'color_product');
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(ProductReview::class);
    }

    public function primaryMedia()
    {
        return $this->mediaAssets()->where('is_primary', true)->orderBy('position');
    }

    public function scopeActive($query)
    {
        if (Schema::hasColumn($this->getTable(), 'status')) {
            return $query->where('status', 'ACTIVE');
        }

        if (Schema::hasColumn($this->getTable(), 'is_active')) {
            return $query->where('is_active', true);
        }

        return $query;
    }

    public function availableStock(): int
    {
        return max(0, (int) $this->stock - (int) $this->reserved_stock);
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

    public function getSummaryAttribute($value): ?string
    {
        $translation = $this->getTranslation('summary_translations', app()->getLocale(), false);

        return $translation ?? $value;
    }

    public function getDescriptionAttribute($value): ?string
    {
        $translation = $this->getTranslation('description_translations', app()->getLocale(), false);

        return $translation ?? $value;
    }
}
