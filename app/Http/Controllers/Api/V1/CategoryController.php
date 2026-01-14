<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Catalog\Actions\ListCategoriesAction;
use Illuminate\Http\Request;

class CategoryController extends ApiController
{
    public function index(Request $request, ListCategoriesAction $action)
    {
        $categories = $action->execute()->map(function ($category) {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
            ];
        });

        return $this->success($categories);
    }
}
