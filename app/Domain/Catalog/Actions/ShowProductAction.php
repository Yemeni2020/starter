<?php

namespace App\Domain\Catalog\Actions;

use App\Models\Product;

class ShowProductAction
{
    public function execute(string $identifier): Product
    {
        $locale = app()->getLocale();
        $otherLocale = $locale === 'ar' ? 'en' : 'ar';

        return Product::query()
            ->with('category')
            ->active()
            ->where(function ($query) use ($identifier, $locale, $otherLocale) {
                // Match slug in current locale
                $query->where("slug_translations->{$locale}", $identifier)
                    // Also allow slug from the other locale (nice UX)
                    ->orWhere("slug_translations->{$otherLocale}", $identifier)
                    // Allow direct ID lookup
                    ->orWhere('id', $identifier);
            })
            ->firstOrFail();
    }
}
