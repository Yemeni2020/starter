<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'currency' => $this->currency,
            'items' => $this->items->map(function ($item) {
                return [
                    'id' => $item->id,
                    'qty' => $item->qty,
                    'price_snapshot' => $item->price_snapshot,
                    'total' => $item->price_snapshot * $item->qty,
                    'product' => [
                        'id' => $item->product?->id,
                        'name' => $item->product?->name,
                        'slug' => $item->product?->slug,
                        'image' => $item->product?->image,
                        'sku' => $item->product?->sku,
                    ],
                ];
            }),
        ];
    }
}
