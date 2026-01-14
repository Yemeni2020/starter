@extends('admin.layouts.app')

@section('content')
    <div class="flex w-full flex-1 flex-col gap-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="space-y-2">
                <div class="inline-flex items-center gap-2 text-xs text-zinc-500">
                    <span class="rounded-full border border-zinc-200 px-2 py-1 dark:border-zinc-700">
                        Product ID: {{ $product->id }}
                    </span>
                    <span class="rounded-full bg-emerald-50 px-2 py-1 text-emerald-700 dark:bg-emerald-500/15 dark:text-emerald-300">
                        {{ $product->is_active ? 'Active' : 'Draft' }}
                    </span>
                </div>
                <flux:heading size="xl" level="1">Edit product</flux:heading>
                <flux:text>Update core details, pricing, and inventory for this item.</flux:text>
            </div>
            <div class="flex flex-wrap gap-3">
                <flux:button variant="outline" :href="route('admin.products.index')" wire:navigate>Back to products</flux:button>
                <flux:button variant="outline" icon="document-duplicate" icon:variant="outline">Duplicate</flux:button>
                <flux:button variant="primary" icon="check" icon:variant="outline" type="submit" form="product-edit-form">Update product</flux:button>
            </div>
        </div>

        @php
            $selectedColorIds = old('color_ids', $product->colorOptions->pluck('id')->toArray());
            if (!is_array($selectedColorIds)) {
                $selectedColorIds = [$selectedColorIds];
            }
            $selectedColorIds = array_map('intval', array_filter($selectedColorIds));

            $existingImages = $product->images;
            if (is_string($existingImages)) {
                $existingImages = json_decode($existingImages, true);
            }
            if (!is_array($existingImages)) {
                $existingImages = [];
            }
            if (!$existingImages && !empty($product->gallery)) {
                $existingImages = is_array($product->gallery) ? $product->gallery : json_decode($product->gallery, true);
            }
            if (!$existingImages && !empty($product->image)) {
                $existingImages = [$product->image];
            }
            $existingImages = array_values(array_filter((array) $existingImages));
        @endphp

        <form id="product-edit-form" class="grid gap-6 lg:grid-cols-[2fr_1fr]" method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="flex flex-col gap-6">
                <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex flex-col gap-4">
                        <flux:heading size="lg" level="2">Product details</flux:heading>
                        @php
                            $locales = ['ar' => 'Arabic', 'en' => 'English'];
                            $defaultLocale = config('app.locale', 'ar');
                            $defaultLocaleSuffix = ucfirst($defaultLocale);
                            $translationValue = function (string $field, string $locale) use ($product) {
                                $old = old("{$field}.{$locale}");
                                if ($old !== null) {
                                    return $old;
                                }

                                return $product->getTranslation("{$field}_translations", $locale, '');
                            };
                        @endphp
                        <div class="flex flex-wrap items-center gap-2">
                            @foreach ($locales as $code => $label)
                                <button
                                    type="button"
                                    class="product-tab rounded-full border border-zinc-200 px-4 py-1 text-sm font-semibold transition {{ $code === $defaultLocale ? 'bg-slate-900 text-white' : 'bg-white text-slate-600 hover:border-slate-400' }}"
                                    data-product-tab="{{ $code }}"
                                >
                                    {{ strtoupper($code) }} {{ $label }}
                                </button>
                            @endforeach
                        </div>
                        <div class="space-y-4 mt-4">
                            <flux:input id="productNameInput" name="name" label="Product name"
                                placeholder="Lumina Desk Lamp"
                                value="{{ old('name', $translationValue('name', $defaultLocale)) }}"
                                data-name-input />

                            @foreach ($locales as $code => $label)
                                <div
                                    class="locale-panel rounded-2xl border border-zinc-200 bg-slate-50 p-4 {{ $code === $defaultLocale ? '' : 'hidden' }}"
                                    data-locale-panel="{{ $code }}"
                                >
                                    <div class="flex items-center justify-between">
                                        <flux:heading size="md" level="3">{{ $label }}</flux:heading>
                                        <span class="text-[11px] font-semibold uppercase tracking-[0.3em] text-slate-400">{{ strtoupper($code) }}</span>
                                    </div>
                                    <flux:input
                                        id="productName{{ ucfirst($code) }}Input"
                                        name="name[{{ $code }}]"
                                        label="Product name"
                                        placeholder="Lumina Desk Lamp"
                                        value="{{ $translationValue('name', $code) }}"
                                        @if($code === $defaultLocale) required data-name-locale-input @endif
                                    />
                                    <flux:input
                                        id="slug{{ ucfirst($code) }}Input"
                                        name="slug[{{ $code }}]"
                                        label="Slug"
                                        placeholder="lumina-desk-lamp"
                                        value="{{ $translationValue('slug', $code) }}"
                                    />
                                    <flux:input
                                        name="summary[{{ $code }}]"
                                        label="Summary"
                                        placeholder="Short summary for cards and listings"
                                        value="{{ $translationValue('summary', $code) }}"
                                    />
                                    <flux:textarea name="description[{{ $code }}]" label="Description" rows="5">{{ $translationValue('description', $code) }}</flux:textarea>
                                </div>
                            @endforeach
                        </div>
                        <div class="grid gap-4 md:grid-cols-2">
                            <flux:select name="category_id" label="Category" required>
                                @foreach ($categories as $category)
                                    <flux:select.option
                                        value="{{ $category->id }}"
                                        :selected="$product->category_id === $category->id"
                                    >
                                        {{ $category->name }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:input name="color" label="Color" placeholder="Black" value="{{ old('color', $product->color) }}" />
                        </div>
                        @include('admin.products.partials.color-options', [
                            'colors' => $colors,
                            'selectedColorIds' => $selectedColorIds,
                        ])
                        <flux:input name="sku" label="SKU" value="{{ $product->sku }}" required />
                    </div>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex flex-col gap-4">
                        <flux:heading size="lg" level="2">Media</flux:heading>

                        @if ($existingImages)
                            <div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                @foreach ($existingImages as $image)
                                    <div class="overflow-hidden rounded-xl border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/60">
                                        <img src="{{ $image }}" alt="Existing product image" class="h-32 w-full object-cover">
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="hidden grid gap-3 sm:grid-cols-2 lg:grid-cols-3" data-image-preview></div>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div class="relative overflow-hidden rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/60">
                                <x-placeholder-pattern class="absolute inset-0 size-full stroke-zinc-400/30 dark:stroke-white/10" />
                                <div class="relative z-10 text-sm text-zinc-500">Primary image</div>
                            </div>
                            <div class="relative overflow-hidden rounded-xl border border-dashed border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/60">
                                <x-placeholder-pattern class="absolute inset-0 size-full stroke-zinc-400/20 dark:stroke-white/10" />
                                <div class="relative z-10 text-sm text-zinc-500">Add more media</div>
                            </div>
                        </div>

                        <flux:input type="file" name="images[]" label="Replace images" multiple data-image-input />
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-6">
                <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex flex-col gap-4">
                        <flux:heading size="lg" level="2">Pricing</flux:heading>
                        <flux:input name="price" label="Price" type="number" step="0.01" value="{{ $product->price }}" required />
                        <flux:input name="compare_at_price" label="Compare at" type="number" step="0.01" value="{{ $product->compare_at_price }}" />
                    </div>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex flex-col gap-4">
                        <flux:heading size="lg" level="2">Inventory</flux:heading>
                        <flux:input name="stock" label="Available stock" type="number" value="{{ $product->stock }}" required />
                        <flux:input name="weight_grams" label="Weight (grams)" type="number" value="{{ $product->weight_grams }}" />
                    </div>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex flex-col gap-4">
                        <flux:heading size="lg" level="2">Status</flux:heading>

                        <flux:select name="is_active" label="Visibility">
                            <flux:select.option value="1" :selected="$product->is_active">Active</flux:select.option>
                            <flux:select.option value="0" :selected="! $product->is_active">Draft</flux:select.option>
                        </flux:select>
                    </div>
                </div>
            </div>
        </form>
    </div>
@endsection


