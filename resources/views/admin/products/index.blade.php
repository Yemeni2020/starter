@extends('admin.layouts.app')

@section('content')
    <div class="flex w-full flex-1 flex-col gap-6">
        <div class="flex flex-col gap-3">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-1">
                    <flux:heading size="xl" level="1">Products</flux:heading>
                    <flux:text>Manage pricing, inventory, and visibility across the catalog.</flux:text>
                </div>
                <div class="flex flex-wrap gap-3">
                    <flux:button variant="outline" icon="arrow-down-tray" icon:variant="outline">Export</flux:button>
                    <flux:button variant="primary" icon="plus" icon:variant="outline" :href="route('admin.products.create')" wire:navigate>
                        Add product
                    </flux:button>
                </div>
            </div>
            <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:text variant="subtle">Total products</flux:text>
                    <flux:heading size="xl" level="2">{{ $stats['total'] ?? 0 }}</flux:heading>
                    <flux:text size="sm" class="mt-2">All catalog items</flux:text>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:text variant="subtle">Active listings</flux:text>
                    <flux:heading size="xl" level="2">{{ $stats['active'] ?? 0 }}</flux:heading>
                    <flux:text size="sm" class="mt-2">Live on storefront</flux:text>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:text variant="subtle">Low stock</flux:text>
                    <flux:heading size="xl" level="2">{{ $stats['low_stock'] ?? 0 }}</flux:heading>
                    <flux:text size="sm" class="mt-2">Stock at or below 5</flux:text>
                </div>
                <div class="rounded-xl border border-zinc-200 bg-white p-4 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:text variant="subtle">Drafts</flux:text>
                    <flux:heading size="xl" level="2">{{ $stats['drafts'] ?? 0 }}</flux:heading>
                    <flux:text size="sm" class="mt-2">Hidden from storefront</flux:text>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-white shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
            <div class="flex flex-col gap-4 border-b border-zinc-200 p-4 dark:border-zinc-700 md:flex-row md:items-center md:justify-between">
                <div class="w-full md:max-w-sm">
                    <flux:input name="product_search" label="Search" placeholder="Search products" icon="magnifying-glass" />
                </div>
                <div class="grid w-full gap-3 sm:grid-cols-2 lg:max-w-2xl lg:grid-cols-3">
                    <flux:select name="status" label="Status">
                        <flux:select.option value="all">All statuses</flux:select.option>
                        <flux:select.option value="active">Active</flux:select.option>
                        <flux:select.option value="draft">Draft</flux:select.option>
                        <flux:select.option value="archived">Archived</flux:select.option>
                    </flux:select>
                    <flux:select name="category" label="Category">
                        <flux:select.option value="all">All categories</flux:select.option>
                        <flux:select.option value="lighting">Lighting</flux:select.option>
                        <flux:select.option value="decor">Decor</flux:select.option>
                        <flux:select.option value="outdoor">Outdoor</flux:select.option>
                    </flux:select>
                    <flux:select name="stock" label="Stock">
                        <flux:select.option value="all">All stock</flux:select.option>
                        <flux:select.option value="in_stock">In stock</flux:select.option>
                        <flux:select.option value="low_stock">Low stock</flux:select.option>
                        <flux:select.option value="out_of_stock">Out of stock</flux:select.option>
                    </flux:select>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 text-sm dark:divide-zinc-700">
                    <thead class="bg-zinc-50 text-xs uppercase tracking-wide text-zinc-500 dark:bg-zinc-800/60 dark:text-zinc-400">
                        <tr>
                            <th scope="col" class="px-4 py-3 text-left font-medium">Product</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium">Category</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium">Price</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium">Stock</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium">Status</th>
                            <th scope="col" class="px-4 py-3 text-left font-medium">Updated</th>
                            <th scope="col" class="px-4 py-3 text-right font-medium">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100 text-zinc-700 dark:divide-zinc-800 dark:text-zinc-300">
                        @forelse ($products as $product)
                            @php
                                $initials = collect(explode(' ', $product->name))
                                    ->filter()
                                    ->map(fn ($chunk) => strtoupper(substr($chunk, 0, 1)))
                                    ->take(2)
                                    ->implode('');
                                $stockStatus = $product->stock === 0 ? 'Out of stock' : ($product->stock <= 5 ? 'Low stock' : 'In stock');
                                $stockClasses = $product->stock === 0
                                    ? 'bg-rose-50 text-rose-700 dark:bg-rose-500/15 dark:text-rose-300'
                                    : ($product->stock <= 5
                                        ? 'bg-amber-50 text-amber-700 dark:bg-amber-500/15 dark:text-amber-300'
                                        : 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300');
                                $statusClasses = $product->is_active
                                    ? 'bg-emerald-50 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300'
                                    : 'bg-zinc-100 text-zinc-600 dark:bg-zinc-700 dark:text-zinc-300';
                            @endphp
                            <tr>
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-zinc-100 text-xs font-semibold text-zinc-500 dark:bg-zinc-800">
                                            {{ $initials ?: 'PR' }}
                                        </div>
                                        <div>
                                            <div class="font-semibold text-zinc-900 dark:text-white">{{ $product->name }}</div>
                                            <div class="text-xs text-zinc-500">SKU {{ $product->sku }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-4">{{ $product->category?->name ?? '-' }}</td>
                                <td class="px-4 py-4">${{ number_format((float) $product->price, 2) }}</td>
                                <td class="px-4 py-4">{{ $product->stock }}</td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $statusClasses }}">
                                        {{ $product->is_active ? 'Active' : 'Draft' }}
                                    </span>
                                    <div class="mt-2">
                                        <span class="inline-flex items-center rounded-full px-2.5 py-1 text-xs font-medium {{ $stockClasses }}">
                                            {{ $stockStatus }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-4">{{ $product->updated_at?->format('M d, Y') }}</td>
                                <td class="px-4 py-4 text-right">
                                    <flux:button variant="ghost" size="sm" :href="route('admin.products.edit', $product)" wire:navigate>Edit</flux:button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-8 text-center text-sm text-zinc-500">No products yet. Create your first product to get started.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="flex flex-col items-center justify-between gap-3 border-t border-zinc-200 px-4 py-3 text-sm text-zinc-500 dark:border-zinc-700 md:flex-row">
                <span>
                    @if ($products->total() > 0)
                        Showing {{ $products->firstItem() }}-{{ $products->lastItem() }} of {{ $products->total() }} products
                    @else
                        Showing 0 products
                    @endif
                </span>
                <div class="flex items-center gap-2">
                    {{ $products->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
