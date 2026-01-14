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
                                        @if($code === $defaultLocale) required @endif
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
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium text-slate-700 dark:text-slate-200">Color options</label>
                            <select
                                name="color_ids[]"
                                multiple
                                class="block h-40 w-full rounded-xl border border-zinc-200 bg-white p-2 text-sm shadow-sm focus:border-blue-500 focus:outline-none focus:ring focus:ring-blue-200 dark:border-zinc-700 dark:bg-zinc-900 dark:text-white"
                                aria-describedby="colorOptionsHelpEdit"
                            >
                                @if ($colors->isEmpty())
                                    <option disabled>No colors defined yet</option>
                                @else
                                    @foreach ($colors as $color)
                                        <option value="{{ $color->id }}" {{ in_array($color->id, $selectedColorIds) ? 'selected' : '' }}>
                                            {{ $color->name }}{{ $color->hex ? " ({$color->hex})" : '' }}
                                        </option>
                                    @endforeach
                                @endif
                            </select>
                            <p id="colorOptionsHelpEdit" class="text-xs text-slate-500 dark:text-slate-400">Hold Ctrl (Windows) or Command (Mac) while selecting to choose multiple colors.</p>
                        </div>
                        <flux:input name="sku" label="SKU" value="{{ $product->sku }}" required />
                    </div>
                </div>

                <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900">
                    <div class="flex flex-col gap-4">
                        <flux:heading size="lg" level="2">Media</flux:heading>

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

                        <flux:input type="file" name="images[]" label="Replace images" multiple />
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

@push('scripts')
    <script>
        (function () {
            const defaultLocale = @json($defaultLocale);
            const defaultLocaleSuffix = @json($defaultLocaleSuffix);

            const initProductEdit = () => {
                const buttons = document.querySelectorAll('[data-product-tab]');
                const panels = document.querySelectorAll('[data-locale-panel]');

                if (buttons.length && panels.length) {
                    const setActive = (target) => {
                        if (!target) return;

                        buttons.forEach((button) => {
                            const code = button.getAttribute('data-product-tab');
                            const isActive = code === target;
                            button.classList.toggle('bg-slate-900', isActive);
                            button.classList.toggle('text-white', isActive);
                            button.classList.toggle('text-slate-600', !isActive);
                            button.classList.toggle('bg-white', !isActive);
                        });

                        panels.forEach((panel) => {
                            panel.classList.toggle('hidden', panel.getAttribute('data-locale-panel') !== target);
                        });
                    };

                    const hasTab = (value) => Array.from(buttons).some((button) => button.getAttribute('data-product-tab') === value);
                    const defaultTab = hasTab(defaultLocale) ? defaultLocale : buttons[0]?.getAttribute('data-product-tab');
                    if (defaultTab) {
                        setActive(defaultTab);
                    }

                    buttons.forEach((button) => {
                        button.addEventListener('click', () => {
                            setActive(button.getAttribute('data-product-tab'));
                        });
                    });
                }

                const nameInput = document.getElementById(`productName${defaultLocaleSuffix}Input`);
                const slugInput = document.getElementById(`slug${defaultLocaleSuffix}Input`);

                if (!nameInput || !slugInput) {
                    return;
                }

                const slugify = (value) =>
                    value
                        .toLowerCase()
                        .trim()
                        .replace(/[^a-z0-9]+/g, '-')
                        .replace(/^-+|-+$/g, '');

                let slugManuallyEdited = false;

                slugInput.addEventListener('input', (event) => {
                    if (!event.isTrusted) return;
                    slugManuallyEdited = slugInput.value.trim().length > 0;
                    if (slugInput.value.trim().length === 0) {
                        slugManuallyEdited = false;
                    }
                });

                nameInput.addEventListener('input', () => {
                    if (slugManuallyEdited && slugInput.value.trim().length > 0) return;
                    slugInput.value = slugify(nameInput.value);
                    slugInput.dispatchEvent(new Event('input', { bubbles: true }));
                });
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initProductEdit);
            } else {
                initProductEdit();
            }
        })();
    </script>
@endpush
