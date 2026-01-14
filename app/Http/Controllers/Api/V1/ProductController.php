<?php

namespace App\Http\Controllers\Api\V1;

use App\Domain\Catalog\Actions\ListProductsAction;
use App\Domain\Catalog\Actions\ShowProductAction;
use App\Http\Resources\ProductResource;
use Illuminate\Http\Request;

class ProductController extends ApiController
{
    public function index(Request $request, ListProductsAction $action)
    {
        $filters = $request->only(['search', 'category', 'sort', 'minPrice', 'maxPrice']);
        $products = $action->execute($filters, (int) $request->input('perPage', 12));

        $payload = ProductResource::collection($products)->response()->getData(true);

        return $this->success($payload);
    }

    public function show(string $slug, ShowProductAction $action)
    {
        $product = $action->execute($slug);

        return $this->success((new ProductResource($product))->resolve());
    }
}
