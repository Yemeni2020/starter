<?php

use App\Support\LocaleSegment;

if (! function_exists('locale_variant_labels')) {
    /**
     * Return the configured labels for each locale segment variant.
     */
    function locale_variant_labels(): array
    {
        return config('app.locale_variant_labels', [
            'ar_sa' => 'العربية (SA)',
            'en_us' => 'English (US)',
        ]);
    }
}

if (! function_exists('current_locale_variant')) {
    /**
     * Resolve the currently active locale variant segment.
     */
    function current_locale_variant(): string
    {
        return session('locale')
            ?? LocaleSegment::normalize(null);
    }
}

if (! function_exists('locale_dropdown_items')) {
    /**
     * Build the structured items for a language dropdown.
     */
function locale_dropdown_items(): array
{
    $current = current_locale_variant();
    return collect(locale_variant_labels())
        ->map(fn ($label, $segment) => [
            'segment' => $segment,
            'label' => $label,
            'is_current' => $current === $segment,
            'route_locale' => LocaleSegment::base($segment),
        ])
        ->values()
        ->all();
}
}
