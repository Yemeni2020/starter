<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryLevel extends Model
{
    use HasFactory;

    protected $fillable = [
        'variant_id',
        'location_id',
        'on_hand',
        'reserved',
    ];

    protected $casts = [
        'on_hand' => 'integer',
        'reserved' => 'integer',
    ];

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function location()
    {
        return $this->belongsTo(InventoryLocation::class, 'location_id');
    }

    public function getAvailableAttribute(): int
    {
        return max(0, $this->on_hand - $this->reserved);
    }
}
