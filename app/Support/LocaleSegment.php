<?php

namespace App\Support;

class LocaleSegment
{
    public static function variantMap(): array
    {
        $supported = config('app.supported_locales', ['ar', 'en']);
        $variants = config('app.locale_variants', []);

        foreach ($supported as $locale) {
            if (! array_key_exists($locale, $variants)) {
                $variants[$locale] = $locale;
            }
        }

        return $variants;
    }

    public static function allowedSegments(): array
    {
        return array_keys(self::variantMap());
    }

    public static function normalize(?string $segment): string
    {
        $default = config('app.locale') ?? 'ar';

        if (! $segment) {
            return $default;
        }

        $segment = strtolower($segment);
        $allowed = self::allowedSegments();

        if (in_array($segment, $allowed, true)) {
            return $segment;
        }

        $base = explode('_', $segment)[0] ?? null;
        if ($base && in_array($base, config('app.supported_locales', ['ar', 'en']), true)) {
            return $segment;
        }

        return $default;
    }

    public static function base(string $segment): string
    {
        $parts = explode('_', $segment);
        $base = $parts[0] ?? (config('app.locale') ?? 'ar');
        $allowed = config('app.supported_locales', ['ar', 'en']);

        return in_array($base, $allowed, true) ? $base : (config('app.locale') ?? 'ar');
    }

    public static function label(string $segment): string
    {
        return config('app.locale_variant_labels', [])[$segment]
            ?? strtoupper(str_replace('_', '-', $segment));
    }
}
