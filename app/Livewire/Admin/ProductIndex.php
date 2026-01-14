<?php

namespace App\Livewire\Admin;

use App\Models\Product;
use Livewire\Component;
use Livewire\WithPagination;

class ProductIndex extends Component
{
    use WithPagination;

    public string $search = '';
    public int $perPage = 15;

    protected $queryString = [
        'search' => ['except' => ''],
    ];

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $query = Product::query()->latest();

        if (trim($this->search) !== '') {
            $term = '%' . trim($this->search) . '%';
            $query->where('name', 'like', $term)
                ->orWhere('slug', 'like', $term)
                ->orWhereRaw("json_extract(name_translations, '$.\"en\"') like ?", [$term])
                ->orWhereRaw("json_extract(name_translations, '$.\"ar\"') like ?", [$term])
                ->orWhereRaw("json_extract(slug_translations, '$.\"en\"') like ?", [$term])
                ->orWhereRaw("json_extract(slug_translations, '$.\"ar\"') like ?", [$term]);
        }

        $products = $query->paginate($this->perPage);

        return view('livewire.admin.product-index', [
            'products' => $products,
        ])->layout('admin.layouts.app', ['title' => 'Products']);
    }
}
