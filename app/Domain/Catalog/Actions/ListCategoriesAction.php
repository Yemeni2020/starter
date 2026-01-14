<?php

namespace App\Domain\Catalog\Actions;

use App\Models\Category;
use Illuminate\Support\Collection;

class ListCategoriesAction
{
    public function execute(): Collection
    {
        return Category::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }
}
