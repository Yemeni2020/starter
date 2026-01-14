<?php

namespace App\Domain\Catalog\Actions;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ListProductsAction
{
    public function execute(array $filters = [], int $perPage = 12, ?int $page = null): LengthAwarePaginator
    {
        $locale = app()->getLocale();
        $localeNamePath = "json_extract(name_translations, '$.\"{$locale}\"')";

        $query = Product::query()->with('category')->active();

        if (!empty($filters['search'])) {
            $search = trim($filters['search']);
            $query->where(function ($q) use ($search, $locale) {
                $q->where("name_translations->{$locale}", 'like', "%{$search}%")
                    ->orWhere("summary_translations->{$locale}", 'like', "%{$search}%")
                    ->orWhere("description_translations->{$locale}", 'like', "%{$search}%");
            });
        }

        if (!empty($filters['category'])) {
            $category = $filters['category'];
            $query->whereHas('category', function ($q) use ($category) {
                $q->where('slug', $category)
                    ->orWhere('id', $category);
            });
        }

        if (!empty($filters['minPrice'])) {
            $query->where('price', '>=', (float) $filters['minPrice']);
        }

        if (!empty($filters['maxPrice'])) {
            $query->where('price', '<=', (float) $filters['maxPrice']);
        }

        $sort = $filters['sort'] ?? null;
        if ($sort === 'price-asc') {
            $query->orderBy('price');
        } elseif ($sort === 'price-desc') {
            $query->orderByDesc('price');
        } elseif ($sort === 'name-asc') {
            $query->orderByRaw($localeNamePath);
        } elseif ($sort === 'name-desc') {
            $query->orderByRaw("{$localeNamePath} DESC");
        } elseif ($sort === 'newest') {
            $query->orderByDesc('created_at');
        } else {
            $query->orderByRaw($localeNamePath);
        }

        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        return $paginator->withQueryString();
    }
}
