@extends('admin.layouts.app')

@section('content')
    @php
        $selectedColorIds = old('color_ids', []);
        if (!is_array($selectedColorIds)) {
            $selectedColorIds = [$selectedColorIds];
        }
        $selectedColorIds = array_map('intval', array_filter($selectedColorIds));

        $locales = ['ar' => 'Arabic', 'en' => 'English'];
        $defaultLocale = config('app.locale', 'ar');
        $defaultLocaleSuffix = ucfirst($defaultLocale);

        $translationValue = function (string $field, string $locale) {
            return old("{$field}.{$locale}") ?? '';
        };
    @endphp

    <div class="mx-auto w-full max-w-7xl px-4 sm:px-6 lg:px-8">
        {{-- Sticky header / actions --}}
        <div
            class="sticky top-0 z-20 -mx-4 sm:-mx-6 lg:-mx-8 border-b border-zinc-200/70 bg-white/80 px-4 py-4 backdrop-blur dark:border-zinc-800/70 dark:bg-zinc-950/70 sm:px-6 lg:px-8">
            <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                <div class="min-w-0">
                    <h1 class="text-xl font-semibold tracking-tight text-zinc-900 dark:text-zinc-50">
                        Create product
                    </h1>
                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">
                        Add a new item to your store catalog and set pricing, inventory, and media.
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <flux:button variant="outline" :href="route('admin.products.index')" wire:navigate>
                        Back
                    </flux:button>

                    <flux:button variant="primary" icon="check" icon:variant="outline" type="submit"
                        form="product-create-form">
                        Save product
                    </flux:button>
                </div>
            </div>
        </div>

        {{-- Page body --}}
        <div class="py-6">
            @if ($errors->any())
                <div
                    class="mb-6 rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 dark:border-rose-900/40 dark:bg-rose-950/40 dark:text-rose-200">
                    <div class="font-semibold">Please fix the following:</div>
                    <ul class="mt-2 list-inside list-disc space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form id="product-create-form" method="POST" action="{{ route('admin.products.store') }}"
                enctype="multipart/form-data" class="grid gap-6 lg:grid-cols-[1fr_360px]">
                @csrf

                {{-- LEFT: main content --}}
                <div class="space-y-6">
                    {{-- Product details --}}
                    <section
                        class="rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="border-b border-zinc-100 p-5 dark:border-zinc-800">
                            <div class="flex items-start justify-between gap-3">
                                <div>
                                    <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-50">Product details</h2>
                                    <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Name, slug, summary, and
                                        description per language.</p>
                                </div>

                                {{-- Locale tabs --}}
                                <div
                                    class="flex rounded-xl border border-zinc-200 bg-zinc-50 p-1 dark:border-zinc-800 dark:bg-zinc-950">
                                    @foreach ($locales as $code => $label)
                                        <button type="button"
                                            class="product-tab px-3 py-1.5 text-sm font-semibold rounded-lg transition
                                                {{ $code === $defaultLocale
                                                    ? 'bg-white text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-zinc-50'
                                                    : 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-50' }}"
                                            data-product-tab="{{ $code }}">
                                            {{ strtoupper($code) }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="p-5 space-y-5">
                            @foreach ($locales as $code => $label)
                                <div class="locale-panel {{ $code === $defaultLocale ? '' : 'hidden' }}"
                                    data-locale-panel="{{ $code }}">
                                    <div class="mb-4 flex items-center justify-between">
                                        <div class="inline-flex items-center gap-2">
                                            <span class="h-2 w-2 rounded-full bg-emerald-500"></span>
                                            <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-50">
                                                {{ $label }}
                                            </div>
                                        </div>
                                        <span class="text-[11px] font-semibold uppercase tracking-[0.25em] text-zinc-400">
                                            {{ strtoupper($code) }}
                                        </span>
                                    </div>

                                    <div class="grid gap-4 md:grid-cols-2">
                                        <flux:input id="productName{{ ucfirst($code) }}Input"
                                            name="name[{{ $code }}]" label="Product name"
                                            placeholder="Lumina Desk Lamp" value="{{ $translationValue('name', $code) }}"
                                            @if ($code === $defaultLocale) required @endif />

                                        <flux:input id="slug{{ ucfirst($code) }}Input" name="slug[{{ $code }}]"
                                            label="Slug" placeholder="lumina-desk-lamp"
                                            value="{{ $translationValue('slug', $code) }}" />
                                    </div>

                                    <div class="grid gap-4 md:grid-cols-2">
                                        <flux:input name="summary[{{ $code }}]" label="Summary"
                                            placeholder="Short summary for cards and listings"
                                            value="{{ $translationValue('summary', $code) }}" />

                                        <div
                                            class="rounded-xl border border-zinc-200 bg-zinc-50 p-3 text-sm text-zinc-600 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-400">
                                            <div class="font-semibold text-zinc-900 dark:text-zinc-50">Tip</div>
                                            Keep summary short (1–2 lines). Use description for full details.
                                        </div>
                                    </div>

                                    <flux:textarea name="description[{{ $code }}]" label="Description"
                                        rows="6" placeholder="Write a short description for the product.">
                                        {{ $translationValue('description', $code) }}</flux:textarea>
                                </div>
                            @endforeach

                            <div class="grid gap-4 md:grid-cols-2">
                                <flux:select name="category_id" label="Category" required>
                                    <flux:select.option value="">Select category</flux:select.option>
                                    @foreach ($categories as $category)
                                        <flux:select.option value="{{ $category->id }}">{{ $category->name }}
                                        </flux:select.option>
                                    @endforeach
                                </flux:select>

                                <flux:input name="color" label="Default color label (optional)" placeholder="Black"
                                    value="{{ old('color') }}" />
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <label class="text-sm font-medium text-zinc-800 dark:text-zinc-200">
                                        Color options <span class="text-rose-500">*</span>
                                    </label>

                                    {{-- Multi-select dropdown --}}
                                    <div class="relative" data-multi-dropdown>
                                        <button type="button"
                                            class="w-full rounded-xl border border-zinc-200 bg-white px-3 py-2 text-left text-sm shadow-sm
                   focus:outline-none focus:ring-4 focus:ring-blue-500/10 focus:border-blue-500
                   dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-50"
                                            data-dropdown-trigger>
                                            <span class="text-zinc-500 dark:text-zinc-400" data-dropdown-placeholder>
                                                Select colors…
                                            </span>

                                            <span class="hidden flex flex-wrap gap-1" data-dropdown-chips></span>

                                            <span
                                                class="pointer-events-none absolute inset-y-0 right-3 flex items-center text-zinc-400">
                                                ▾
                                            </span>
                                        </button>

                                        <div class="absolute z-30 mt-2 hidden w-full overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-lg
                   dark:border-zinc-800 dark:bg-zinc-950"
                                            data-dropdown-panel>
                                            <div class="max-h-64 overflow-auto p-2">
                                                @forelse ($colors as $color)
                                                    <label
                                                        class="flex cursor-pointer items-center gap-3 rounded-xl px-3 py-2 hover:bg-zinc-50 dark:hover:bg-zinc-900">
                                                        <input type="checkbox"
                                                            class="h-4 w-4 rounded border-zinc-300 text-blue-600 focus:ring-blue-500/20 dark:border-zinc-700"
                                                            data-color-checkbox value="{{ $color->id }}"
                                                            data-color-name="{{ $color->name }}"
                                                            @checked(in_array($color->id, $selectedColorIds)) />
                                                        <span class="flex items-center gap-2">
                                                            @if ($color->hex)
                                                                <span
                                                                    class="h-4 w-4 rounded-full border border-zinc-200 dark:border-zinc-700"
                                                                    style="background: {{ $color->hex }}"></span>
                                                            @endif
                                                            <span class="text-sm text-zinc-800 dark:text-zinc-100">
                                                                {{ $color->name }}
                                                                @if ($color->hex)
                                                                    <span
                                                                        class="text-xs text-zinc-500 dark:text-zinc-400">({{ $color->hex }})</span>
                                                                @endif
                                                            </span>
                                                        </span>
                                                    </label>
                                                @empty
                                                    <div class="px-3 py-2 text-sm text-zinc-500 dark:text-zinc-400">
                                                        No colors defined yet
                                                    </div>
                                                @endforelse
                                            </div>

                                            <div
                                                class="flex items-center justify-between gap-2 border-t border-zinc-100 p-2 dark:border-zinc-800">
                                                <button type="button"
                                                    class="rounded-lg px-3 py-1.5 text-xs font-semibold text-zinc-600 hover:bg-zinc-50 dark:text-zinc-300 dark:hover:bg-zinc-900"
                                                    data-clear>
                                                    Clear
                                                </button>
                                                <button type="button"
                                                    class="rounded-lg bg-zinc-900 px-3 py-1.5 text-xs font-semibold text-white dark:bg-white dark:text-zinc-900"
                                                    data-done>
                                                    Done
                                                </button>
                                            </div>
                                        </div>

                                        {{-- Hidden inputs generated by JS --}}
                                        <div data-hidden-inputs></div>

                                        @error('color_ids')
                                            <p class="mt-1 text-sm text-rose-600">{{ $message }}</p>
                                        @enderror

                                        <p class="text-xs text-zinc-500 dark:text-zinc-400">
                                            Select at least one color (required).
                                        </p>
                                    </div>
                                </div>

                                <div class="space-y-2">
                                    <div class="flex items-end gap-2">
                                        <flux:input id="skuInput" name="sku" label="SKU" placeholder="PRD-1042"
                                            required />
                                        <flux:button id="generateSkuButton" type="button" variant="outline">
                                            Generate
                                        </flux:button>
                                    </div>

                                    <div
                                        class="rounded-xl border border-zinc-200 bg-zinc-50 p-3 text-xs text-zinc-600 dark:border-zinc-800 dark:bg-zinc-950 dark:text-zinc-400">
                                        SKU is your internal identifier. Keep it unique.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>

                    {{-- Media --}}
                    <section
                        class="rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="border-b border-zinc-100 p-5 dark:border-zinc-800">
                            <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-50">Media</h2>
                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Upload product images (multiple
                                supported).</p>
                        </div>

                        <div class="p-5 space-y-4">
                            <flux:input type="file" name="images[]" label="Product images" multiple />

                            <div
                                class="rounded-2xl border border-dashed border-zinc-300 bg-zinc-50 p-6 text-center dark:border-zinc-700 dark:bg-zinc-950">
                                <div class="mx-auto max-w-md space-y-2">
                                    <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-50">
                                        Drag & drop images here
                                    </div>
                                    <div class="text-sm text-zinc-600 dark:text-zinc-400">
                                        or click above to upload. Recommended <span class="font-semibold">1600×1200</span>.
                                    </div>
                                    <div class="text-xs text-zinc-500 dark:text-zinc-500">
                                        JPG/PNG/WebP. Keep file size optimized for faster loading.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                {{-- RIGHT: sidebar --}}
                <aside class="space-y-6">
                    <section
                        class="rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="border-b border-zinc-100 p-5 dark:border-zinc-800">
                            <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-50">Pricing</h2>
                        </div>
                        <div class="p-5 space-y-4">
                            <flux:input name="price" label="Price" type="number" step="0.01"
                                placeholder="129.00" required />
                            <flux:input name="compare_at_price" label="Compare at" type="number" step="0.01"
                                placeholder="149.00" />
                            <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                “Compare at” shows discount pricing (optional).
                            </div>
                        </div>
                    </section>

                    <section
                        class="rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="border-b border-zinc-100 p-5 dark:border-zinc-800">
                            <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-50">Inventory</h2>
                        </div>
                        <div class="p-5 space-y-4">
                            <flux:input name="stock" label="Available stock" type="number" placeholder="84"
                                required />
                            <flux:input name="weight_grams" label="Weight (grams)" type="number" placeholder="850" />
                        </div>
                    </section>

                    <section
                        class="rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-900">
                        <div class="border-b border-zinc-100 p-5 dark:border-zinc-800">
                            <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-50">Status</h2>
                        </div>
                        <div class="p-5 space-y-4">
                            <flux:select name="is_active" label="Visibility">
                                <flux:select.option value="1">Active</flux:select.option>
                                <flux:select.option value="0">Draft</flux:select.option>
                            </flux:select>

                            <div
                                class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-950">
                                <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-50">Badges</div>
                                <div class="mt-3 grid gap-3">
                                    <flux:checkbox name="is_best_seller" label="Best Sellers" />
                                    <flux:checkbox name="is_new_arrival" label="New Arrivals" />
                                    <flux:checkbox name="is_trending_now" label="Trending Now" />
                                </div>
                            </div>
                        </div>
                    </section>
                </aside>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
    <script>
        (function() {
            const defaultLocale = @json($defaultLocale);
            const defaultLocaleSuffix = @json($defaultLocaleSuffix);

            const initProductCreate = () => {
                const generateSkuButton = document.getElementById('generateSkuButton');
                const skuInput = document.getElementById('skuInput');
                const nameInput = document.getElementById(`productName${defaultLocaleSuffix}Input`);
                const slugInput = document.getElementById(`slug${defaultLocaleSuffix}Input`);

                const slugify = (value) =>
                    (value || '')
                    .toLowerCase()
                    .trim()
                    .replace(/[^a-z0-9]+/g, '-')
                    .replace(/^-+|-+$/g, '');

                let slugManuallyEdited = false;

                if (slugInput) {
                    slugInput.addEventListener('input', (event) => {
                        if (!event.isTrusted) return;
                        slugManuallyEdited = slugInput.value.trim().length > 0;
                        if (slugInput.value.trim().length === 0) {
                            slugManuallyEdited = false;
                        }
                    });
                }

                if (nameInput && slugInput) {
                    nameInput.addEventListener('input', () => {
                        if (slugManuallyEdited && slugInput.value.trim().length > 0) return;
                        slugInput.value = slugify(nameInput.value);
                        slugInput.dispatchEvent(new Event('input', {
                            bubbles: true
                        }));
                    });
                }

                if (generateSkuButton && skuInput) {
                    const generateSku = () => {
                        const stamp = Date.now().toString().slice(-4);
                        const random = Math.random().toString(36).slice(2, 6).toUpperCase();
                        return `PRD-${stamp}${random}`;
                    };

                    generateSkuButton.addEventListener('click', () => {
                        skuInput.value = generateSku();
                        skuInput.dispatchEvent(new Event('input', {
                            bubbles: true
                        }));
                    });
                }

                const buttons = document.querySelectorAll('[data-product-tab]');
                const panels = document.querySelectorAll('[data-locale-panel]');

                if (!buttons.length || !panels.length) return;

                const setActive = (target) => {
                    if (!target) return;

                    buttons.forEach((button) => {
                        const code = button.getAttribute('data-product-tab');
                        const isActive = code === target;

                        button.classList.toggle('bg-white', isActive);
                        button.classList.toggle('text-zinc-900', isActive);
                        button.classList.toggle('shadow-sm', isActive);
                        button.classList.toggle('text-zinc-600', !isActive);
                        button.classList.toggle('hover:text-zinc-900', !isActive);
                        button.classList.toggle('dark:bg-zinc-900', isActive);
                        button.classList.toggle('dark:text-zinc-50', isActive);
                        button.classList.toggle('dark:text-zinc-400', !isActive);
                        button.classList.toggle('dark:hover:text-zinc-50', !isActive);
                    });

                    panels.forEach((panel) => {
                        panel.classList.toggle('hidden', panel.getAttribute('data-locale-panel') !==
                            target);
                    });
                };

                const hasTab = (value) => Array.from(buttons).some((button) => button.getAttribute(
                    'data-product-tab') === value);
                const defaultTab = hasTab(defaultLocale) ? defaultLocale : buttons[0]?.getAttribute(
                    'data-product-tab');
                if (defaultTab) {
                    setActive(defaultTab);
                }

                buttons.forEach((button) => {
                    button.addEventListener('click', () => {
                        setActive(button.getAttribute('data-product-tab'));
                    });
                });
            };

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initProductCreate);
            } else {
                initProductCreate();
            }
        })();
        document.addEventListener('DOMContentLoaded', () => {
            document.querySelectorAll('[data-multi-dropdown]').forEach((root) => {
                const trigger = root.querySelector('[data-dropdown-trigger]');
                const panel = root.querySelector('[data-dropdown-panel]');
                const placeholder = root.querySelector('[data-dropdown-placeholder]');
                const chipsWrap = root.querySelector('[data-dropdown-chips]');
                const hiddenWrap = root.querySelector('[data-hidden-inputs]');
                const checkboxes = Array.from(root.querySelectorAll('[data-color-checkbox]'));
                const clearBtn = root.querySelector('[data-clear]');
                const doneBtn = root.querySelector('[data-done]');

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

                const render = () => {
                    const selected = checkboxes.filter(cb => cb.checked).map(cb => ({
                        id: cb.value,
                        name: cb.dataset.colorName || cb.value
                    }));

                    if (selected.length === 0) {
                        placeholder.classList.remove('hidden');
                        chipsWrap.classList.add('hidden');
                        chipsWrap.innerHTML = '';
                    } else {
                        placeholder.classList.add('hidden');
                        chipsWrap.classList.remove('hidden');
                        chipsWrap.innerHTML = selected.map(s =>
                            `<span class="inline-flex items-center rounded-full border border-zinc-200 bg-zinc-50 px-2 py-0.5 text-xs font-semibold text-zinc-700
                 dark:border-zinc-700 dark:bg-zinc-900 dark:text-zinc-200">${s.name}</span>`
                        ).join('');
                    }

                    syncHiddenInputs(selected.map(s => s.id));
                };

                // initial render (supports old() selections)
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

                // click outside closes
                document.addEventListener('click', (e) => {
                    if (!root.contains(e.target)) close();
                });
            });

            // REQUIRED validation on submit
            const form = document.getElementById('product-create-form');
            if (form) {
                form.addEventListener('submit', (e) => {
                    const hasAny = form.querySelectorAll('input[name="color_ids[]"]').length > 0;
                    if (!hasAny) {
                        e.preventDefault();
                        alert('Please select at least one color.');
                    }
                });
            }
        });
    </script>
@endpush
