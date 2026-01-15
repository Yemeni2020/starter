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
                                            data-product-tab="{{ $code }}"
                                            data-tab-active="bg-white text-zinc-900 shadow-sm dark:bg-zinc-900 dark:text-zinc-50"
                                            data-tab-inactive="text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-50">
                                            {{ strtoupper($code) }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <div class="p-5 space-y-5">
                            {{-- UI name input (syncs to default locale) --}}
                            <flux:input id="productNameInput" name="name_ui" label="Product name"
                                placeholder="Lumina Desk Lamp" value="{{ old('name_ui') }}" data-name-input />

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
                                            @if ($code === $defaultLocale) required data-name-locale-input @endif />

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
                                        rows="6" placeholder="Write a short description for the product.">{{ $translationValue('description', $code) }}</flux:textarea>
                                </div>
                            @endforeach

                            {{-- SEO overrides --}}
                            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-950">
                                <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-50">{{ __('SEO overrides') }}</div>
                                <div class="mt-3 grid gap-4">
                                    <flux:input name="meta_title" label="{{ __('Meta title') }}" value="{{ old('meta_title') }}" />
                                    <flux:textarea name="meta_description" label="{{ __('Meta description') }}" rows="3">{{ old('meta_description') }}</flux:textarea>
                                    <flux:input name="canonical_url" label="{{ __('Canonical URL') }}" type="url" value="{{ old('canonical_url') }}" />
                                </div>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <flux:select name="category_id" label="Category" required>
                                    <flux:select.option value="">Select category</flux:select.option>
                                    @foreach ($categories as $category)
                                        <flux:select.option value="{{ $category->id }}">{{ $category->name }}</flux:select.option>
                                    @endforeach
                                </flux:select>

                                <flux:input name="color" label="Default color label (optional)" placeholder="Black"
                                    value="{{ old('color') }}" />
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                {{-- Color options partial --}}
                                @include('admin.products.partials.color-options', [
                                    'colors' => $colors,
                                    'selectedColorIds' => $selectedColorIds,
                                ])

                                <div class="space-y-2">
                                    <div class="flex items-end gap-2">
                                        <flux:input id="skuInput" name="sku" label="SKU" placeholder="PRD-1042" required />
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
                            <p class="mt-1 text-sm text-zinc-600 dark:text-zinc-400">Upload product images (multiple supported).</p>
                        </div>

                        <div class="p-5 space-y-4">
                            <flux:input type="file" name="images[]" label="Product images" multiple data-image-input />

                            <div class="hidden grid gap-3 sm:grid-cols-2 lg:grid-cols-3" data-image-preview></div>

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
                            <flux:input name="price" label="Price" type="number" step="0.01" placeholder="129.00" required />
                            <flux:input name="compare_at_price" label="Compare at" type="number" step="0.01" placeholder="149.00" />
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
                            <flux:input name="stock" label="Available stock" type="number" placeholder="84" required />
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

                            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-950">
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
(function () {
    const defaultLocale = @json($defaultLocale);
    const defaultLocaleSuffix = @json($defaultLocaleSuffix);

    // IMPORTANT: if you have $locales in blade, pass it to JS for safe looping
    const localesMap = @json($locales); // { ar: "Arabic", en: "English" }
    const localeCodes = Object.keys(localesMap || {});

    const slugify = (value) =>
        (value || '')
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');

    const initProductCreate = () => {
        // SKU
        const generateSkuButton = document.getElementById('generateSkuButton');
        const skuInput = document.getElementById('skuInput');

        if (generateSkuButton && skuInput) {
            const generateSku = () => {
                const stamp = Date.now().toString().slice(-4);
                const random = Math.random().toString(36).slice(2, 6).toUpperCase();
                return `PRD-${stamp}${random}`;
            };

            generateSkuButton.addEventListener('click', () => {
                skuInput.value = generateSku();
                skuInput.dispatchEvent(new Event('input', { bubbles: true }));
            });
        }

        // Locale tabs (use data-tab-active/inactive)
        const buttons = document.querySelectorAll('[data-product-tab]');
        const panels = document.querySelectorAll('[data-locale-panel]');

        // --- NAME SYNC HELPERS (UI name <-> active locale name + slug) ---
        const uiNameInput = document.querySelector('[data-name-input]');

        const getActiveLocale = () => {
            const activeBtn = document.querySelector('[data-product-tab].is-active');
            return activeBtn?.getAttribute('data-product-tab') || defaultLocale;
        };

        const cap = (s) => (s || '').charAt(0).toUpperCase() + (s || '').slice(1);

        const getLocaleNameInput = (locale) =>
            document.getElementById(`productName${cap(locale)}Input`);

        const getLocaleSlugInput = (locale) =>
            document.getElementById(`slug${cap(locale)}Input`);

        const setUiFromLocale = (locale) => {
            if (!uiNameInput) return;
            const nameInput = getLocaleNameInput(locale);
            uiNameInput.value = nameInput?.value || '';
        };

        // Track manual slug per locale
        const slugManual = new Map();
        const isSlugManual = (locale) => slugManual.get(locale) === true;

        const bindSlugManualTracking = (locale) => {
            const slugInput = getLocaleSlugInput(locale);
            if (!slugInput) return;

            slugInput.addEventListener('input', (event) => {
                if (!event.isTrusted) return;
                const v = slugInput.value.trim();
                slugManual.set(locale, v.length > 0);
                if (v.length === 0) slugManual.set(locale, false);
            });
        };

        // Bind slug tracking for all locales
        localeCodes.forEach((locale) => bindSlugManualTracking(locale));

        // When user types in UI name: update ACTIVE locale name + slug
        if (uiNameInput) {
            uiNameInput.addEventListener('input', () => {
                const locale = getActiveLocale();
                const nameInput = getLocaleNameInput(locale);
                const slugInput = getLocaleSlugInput(locale);

                if (nameInput) {
                    nameInput.value = uiNameInput.value;
                    nameInput.dispatchEvent(new Event('input', { bubbles: true }));
                }

                if (slugInput && !isSlugManual(locale)) {
                    slugInput.value = slugify(uiNameInput.value);
                    slugInput.dispatchEvent(new Event('input', { bubbles: true }));
                }
            });
        }
        // --- END NAME SYNC HELPERS ---

        const setActive = (target) => {
            buttons.forEach((btn) => {
                const code = btn.getAttribute('data-product-tab');
                const isActive = code === target;

                // mark active button
                btn.classList.toggle('is-active', isActive);

                const active = (btn.getAttribute('data-tab-active') || '').split(' ').filter(Boolean);
                const inactive = (btn.getAttribute('data-tab-inactive') || '').split(' ').filter(Boolean);

                // remove both sets first (avoid leftovers)
                [...active, ...inactive].forEach(cls => btn.classList.remove(cls));

                if (isActive) active.forEach(cls => btn.classList.add(cls));
                else inactive.forEach(cls => btn.classList.add(cls));
            });

            panels.forEach((panel) => {
                panel.classList.toggle('hidden', panel.getAttribute('data-locale-panel') !== target);
            });

            // IMPORTANT: update top UI name when switching locale tab
            setUiFromLocale(target);
        };

        const hasTab = (value) => Array.from(buttons).some((b) => b.getAttribute('data-product-tab') === value);
        const defaultTab = hasTab(defaultLocale) ? defaultLocale : buttons[0]?.getAttribute('data-product-tab');
        if (defaultTab) setActive(defaultTab);

        buttons.forEach((btn) =>
            btn.addEventListener('click', () => setActive(btn.getAttribute('data-product-tab')))
        );

        // Initial fill of UI name from active tab
        if (defaultTab) setUiFromLocale(defaultTab);
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initProductCreate);
    } else {
        initProductCreate();
    }
})();

// Image preview
document.addEventListener('DOMContentLoaded', () => {
    const input = document.querySelector('[data-image-input]');
    const preview = document.querySelector('[data-image-preview]');
    if (!input || !preview) return;

    const render = (files) => {
        preview.innerHTML = '';
        if (!files || files.length === 0) {
            preview.classList.add('hidden');
            return;
        }
        preview.classList.remove('hidden');

        Array.from(files).forEach((file) => {
            if (!file.type.startsWith('image/')) return;

            const url = URL.createObjectURL(file);

            const card = document.createElement('div');
            card.className =
                'group overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm dark:border-zinc-800 dark:bg-zinc-950';

            card.innerHTML = `
                <div class="aspect-[4/3] overflow-hidden bg-zinc-100 dark:bg-zinc-900">
                    <img src="${url}" alt="" class="h-full w-full object-cover transition group-hover:scale-[1.02]" />
                </div>
                <div class="p-3">
                    <div class="truncate text-xs font-semibold text-zinc-700 dark:text-zinc-200">${file.name}</div>
                    <div class="mt-1 text-[11px] text-zinc-500 dark:text-zinc-400">${Math.round(file.size / 1024)} KB</div>
                </div>
            `;

            preview.appendChild(card);
        });
    };

    input.addEventListener('change', () => render(input.files));
});
</script>
@endpush

