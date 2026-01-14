<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttributeValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'definition_id',
        'product_id',
        'variant_id',
        'text_value',
        'number_value',
        'boolean_value',
        'json_value',
    ];

    protected $casts = [
        'number_value' => 'decimal:4',
        'boolean_value' => 'boolean',
        'json_value' => 'array',
    ];

    public function definition()
    {
        return $this->belongsTo(AttributeDefinition::class, 'definition_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function value()
    {
        if ($this->text_value !== null) {
            return $this->text_value;
        }

        if ($this->number_value !== null) {
            return $this->number_value;
        }

        if ($this->boolean_value !== null) {
            return $this->boolean_value;
        }

        if ($this->json_value !== null) {
            return $this->json_value;
        }

        return null;
    }
}
