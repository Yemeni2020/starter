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

@push('scripts')
    <script>
        (function () {
            const defaultLocale = @json($defaultLocale);
            const defaultLocaleSuffix = @json($defaultLocaleSuffix);

            const slugify = (value) =>
                (value || '')
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');

            const initMultiDropdowns = (form) => {
                form.querySelectorAll('[data-multi-dropdown]').forEach((root) => {
                    if (root.dataset.jsInit === '1') return;
                    root.dataset.jsInit = '1';

                    const trigger = root.querySelector('[data-dropdown-trigger]');
                    const panel = root.querySelector('[data-dropdown-panel]');
                    const placeholder = root.querySelector('[data-dropdown-placeholder]');
                    const chipsWrap = root.querySelector('[data-dropdown-chips]');
                    const hiddenWrap = root.querySelector('[data-hidden-inputs]');
                    const checkboxes = Array.from(root.querySelectorAll('[data-color-checkbox]'));
                    const clearBtn = root.querySelector('[data-clear]');
                    const doneBtn = root.querySelector('[data-done]');

                    if (!trigger || !panel || !hiddenWrap) return;

                    const open = () => panel.classList.remove('hidden');
                    const close = () => panel.classList.add('hidden');
                    const toggle = () => panel.classList.toggle('hidden');

                    const syncHiddenInputs = (ids) => {
                        hiddenWrap.innerHTML = '';
                        ids.forEach((id) => {
                            const input = document.createElement('input');
                            input.type = 'hidden';
                            input.name = 'color_ids[]';
                            input.value = id;
                            hiddenWrap.appendChild(input);
                        });
                    };
            const initImagePreview = (form) => {
                const input = form.querySelector('[data-image-input]');
                const preview = form.querySelector('[data-image-preview]');
                if (!input || !preview) return;

                const render = () => {
                    preview.innerHTML = '';
                    const files = Array.from(input.files || []);
                    if (files.length === 0) {
                        preview.classList.add('hidden');
                        return;
                    }
                    preview.classList.remove('hidden');
                    files.forEach((file) => {
                        const url = URL.createObjectURL(file);
                        const wrapper = document.createElement('div');
                        wrapper.className = 'overflow-hidden rounded-xl border border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-800/60';
                        const img = document.createElement('img');
                        img.src = url;
                        img.alt = 'Selected product image';
                        img.className = 'h-32 w-full object-cover';
                        img.onload = () => URL.revokeObjectURL(url);
                        wrapper.appendChild(img);
                        preview.appendChild(wrapper);
                    });
                };

                input.addEventListener('change', render);
                render();
            };


                input.addEventListener('change', render);
                render();
            };

                                const render = () => {
                        const selected = checkboxes.filter(cb => cb.checked).map(cb => ({
                            id: cb.value,
                            name: cb.dataset.colorName || cb.value
                        }));

                        if (selected.length === 0) {
                            placeholder?.classList.remove('hidden');
                            chipsWrap?.classList.add('hidden');
                            if (chipsWrap) chipsWrap.innerHTML = '';
                        } else {
                            placeholder?.classList.add('hidden');
                            chipsWrap?.classList.remove('hidden');
                            if (chipsWrap) {
                                chipsWrap.innerHTML = selected.map(s =>
                                    `<span class="inline-flex items-center rounded-full border border-zinc-200 bg-zinc-50 px-2 py-0.5 text-xs font-semibold text-zinc-700 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200">${s.name}</span>`
                                ).join('');
                            }
                        }

                        syncHiddenInputs(selected.map(s => s.id));
                    };

                    render();

                    trigger.addEventListener('click', (e) => {
                        e.preventDefault();
                        toggle();
                    });

                    doneBtn?.addEventListener('click', close);

                    clearBtn?.addEventListener('click', () => {
                        checkboxes.forEach(cb => cb.checked = false);
                        render();
                    });

                    checkboxes.forEach(cb => cb.addEventListener('change', render));

                    document.addEventListener('click', (e) => {
                        if (!root.contains(e.target)) close();
                    });
                });
            };

            const initProductEdit = () => {
                const form = document.getElementById('product-edit-form');
                if (!form || form.dataset.jsInit === '1') return;
                form.dataset.jsInit = '1';

                const buttons = form.querySelectorAll('[data-product-tab]');
                const panels = form.querySelectorAll('[data-locale-panel]');

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

                const nameInput = document.querySelector('[data-name-input]') ||
                    document.getElementById(`productName${defaultLocaleSuffix}Input`);
                const localeNameInput = document.querySelector('[data-name-locale-input]') ||
                    document.getElementById(`productName${defaultLocaleSuffix}Input`);
                const slugInput = document.getElementById(`slug${defaultLocaleSuffix}Input`);

                if (nameInput && slugInput) {
                    let slugTouched = false;
                    let slugTimer = null;

                    const debounce = (callback, delay = 300) => {
                        return (...args) => {
                            if (slugTimer) {
                                window.clearTimeout(slugTimer);
                            }
                            slugTimer = window.setTimeout(() => callback(...args), delay);
                        };
                    };

                    slugInput.addEventListener('input', (event) => {
                        if (!event.isTrusted) return;
                        slugTouched = slugInput.value.trim().length > 0;
                    });

                    const updateSlug = debounce(() => {
                        if (slugTouched && slugInput.value.trim().length > 0) return;
                        if (localeNameInput && localeNameInput !== nameInput) {
                            localeNameInput.value = nameInput.value;
                        }
                        slugInput.value = slugify(nameInput.value);
                        slugInput.dispatchEvent(new Event('input', { bubbles: true }));
                    });
                    nameInput.addEventListener('input', updateSlug);
                }

                initMultiDropdowns(form);

                initImagePreview(form);

                form.addEventListener('submit', (e) => {
                    const hasAny = form.querySelectorAll('input[name="color_ids[]"]').length > 0;
                    if (!hasAny) {
                        e.preventDefault();
                        alert('Please select at least one color.');
                    }
                });
            };

            document.addEventListener('DOMContentLoaded', initProductEdit);
            document.addEventListener('livewire:navigated', initProductEdit);
        })();
    </script>
@endpush

