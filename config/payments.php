<?php

return [
    'default' => env('PAYMENTS_DEFAULT_PROVIDER', 'mock'),
    'mock_mode' => filter_var(env('PAYMENTS_MOCK_MODE', true), FILTER_VALIDATE_BOOLEAN),
    'callback_url' => env('PAYMENTS_CALLBACK_URL', env('APP_URL').'/payments/return/{provider}'),
    'return_url' => env('PAYMENTS_RETURN_URL', env('APP_URL').'/payments/return/{provider}'),
    'cancel_url' => env('PAYMENTS_CANCEL_URL', env('APP_URL').'/payments/cancel/{provider}'),
    'webhook_secret' => env('PAYMENTS_WEBHOOK_SECRET', ''),
    'providers' => [
        'mada' => [
            'merchant_id' => env('PAYMENTS_MADA_MERCHANT_ID'),
            'secret' => env('PAYMENTS_MADA_SECRET'),
        ],
        'stcpay' => [
            'merchant_id' => env('PAYMENTS_STCPAY_MERCHANT_ID'),
            'secret' => env('PAYMENTS_STCPAY_SECRET'),
        ],
        'mock' => [
            'enabled' => true,
        ],
    ],
];
