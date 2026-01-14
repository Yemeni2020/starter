<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SecuritySetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug',
        'recaptcha_enabled',
        'recaptcha_site_key',
        'recaptcha_secret_key',
        'social',
    ];

    protected $casts = [
        'recaptcha_enabled' => 'boolean',
        'social' => 'array',
    ];
}
