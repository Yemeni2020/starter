<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentGatewaySetting extends Model
{
    protected $fillable = [
        'provider',
        'display_name',
        'enabled',
        'sandbox',
        'credentials',
        'webhook_secret',
        'sort_order',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'sandbox' => 'boolean',
        'sort_order' => 'integer',
        'credentials' => 'encrypted:array',
        'webhook_secret' => 'encrypted',
    ];
}
