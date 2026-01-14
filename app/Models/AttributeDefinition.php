<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttributeDefinition extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'label_translations',
        'type',
        'unit',
        'description',
        'options',
        'is_variant_specific',
        'is_required',
        'sort_order',
    ];

    protected $casts = [
        'label_translations' => 'array',
        'options' => 'array',
        'is_variant_specific' => 'boolean',
        'is_required' => 'boolean',
    ];

    public function values()
    {
        return $this->hasMany(AttributeValue::class, 'definition_id');
    }

    public function label(string $locale = null): string
    {
        $locale = $locale ?? app()->getLocale();
        return $this->label_translations[$locale] ?? $this->label_translations['en'] ?? $this->key;
    }
}
