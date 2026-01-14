<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SeoSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'route_name',
        'title',
        'description',
        'image',
        'locale',
        'meta',
        'json_ld',
    ];

    protected $casts = [
        'meta' => 'array',
        'json_ld' => 'array',
    ];

    public function scopeForRoute($query, ?string $routeName)
    {
        if (!$routeName) {
            return $query;
        }

        return $query->where('route_name', $routeName);
    }
}
