<?php

return [
    'currency' => env('STORE_CURRENCY', 'SAR'),
    'shipping_fee' => (float) env('STORE_SHIPPING_FEE', 0),
    'tax_rate' => (float) env('STORE_TAX_RATE', 0),
];
