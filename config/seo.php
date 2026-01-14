<?php

return [
    'site_title' => env('SEO_SITE_TITLE', 'Otex Automotives'),
    'site_description' => env('SEO_SITE_DESCRIPTION', 'Premium parts and accessories for your ride. Shop performance, styling, and more.'),
    'site_url' => env('APP_URL', 'https://example.com'),
    'twitter' => [
        'site' => env('SEO_TWITTER_SITE', '@otex_partners'),
        'card' => 'summary_large_image',
    ],
    'facebook' => [
        'app_id' => env('SEO_FACEBOOK_APP_ID'),
    ],
    'default_image' => env('SEO_DEFAULT_IMAGE', env('APP_URL') . '/img/og-default.jpg'),
    'locale' => env('APP_LOCALE', 'en_US'),
];
