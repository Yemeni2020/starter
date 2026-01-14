@extends('admin.layouts.app')

@section('content')
    @php
        $locales = config('app.supported_locales', ['en', 'ar']);
        $currencies = ['USD', 'SAR', 'EUR', 'GBP'];
        $timezones = ['UTC', 'Asia/Riyadh', 'America/New_York', 'Europe/London'];
    @endphp

    <div class="flex w-full flex-1 flex-col gap-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="space-y-1">
                <flux:heading size="xl" level="1">{{ __('Settings') }}</flux:heading>
                <flux:text>{{ __('Configure core store preferences, translations, and integrations.') }}</flux:text>
            </div>
        </div>

        @if (session('status'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <div class="flex flex-wrap gap-3 rounded-full border border-slate-200 bg-white px-4 py-2 shadow-inner" role="tablist">
            <button type="button" data-tab-target="general" class="tab-toggle rounded-full px-4 py-2 text-sm font-semibold text-slate-800 transition hover:bg-slate-100 focus:outline-none" aria-selected="true">{{ __('General') }}</button>
            <button type="button" data-tab-target="seo" class="tab-toggle rounded-full px-4 py-2 text-sm font-semibold text-slate-800 transition hover:bg-slate-100 focus:outline-none">{{ __('SEO') }}</button>
            <button type="button" data-tab-target="payments" class="tab-toggle rounded-full px-4 py-2 text-sm font-semibold text-slate-800 transition hover:bg-slate-100 focus:outline-none">{{ __('Payments') }}</button>
            <button type="button" data-tab-target="translations" class="tab-toggle rounded-full px-4 py-2 text-sm font-semibold text-slate-800 transition hover:bg-slate-100 focus:outline-none">{{ __('Translations') }}</button>
            <button type="button" data-tab-target="reports" class="tab-toggle rounded-full px-4 py-2 text-sm font-semibold text-slate-800 transition hover:bg-slate-100 focus:outline-none">{{ __('Reports') }}</button>
        </div>

        <div class="grid gap-6">
            <div class="tab-panel rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900" data-tab="general">
                <form method="POST" action="{{ route('admin.settings.general') }}" enctype="multipart/form-data" class="grid gap-6 lg:grid-cols-[2fr_1fr]">
                    @csrf

                    <div class="space-y-5">
                        <flux:heading size="lg" level="2">{{ __('General settings') }}</flux:heading>
                        <flux:input name="site_name" label="{{ __('Site name') }}" value="{{ old('site_name', $generalSettings['site_name'] ?? '') }}" />
                        <flux:input name="site_url" label="{{ __('Site URL') }}" type="url" value="{{ old('site_url', $generalSettings['site_url'] ?? '') }}" />
                        <flux:input name="support_email" label="{{ __('Support email') }}" type="email" value="{{ old('support_email', $generalSettings['support_email'] ?? '') }}" />

                        <div class="grid gap-4 md:grid-cols-2">
                            <flux:select name="default_currency" label="{{ __('Default currency') }}">
                                @foreach ($currencies as $currency)
                                    <flux:select.option value="{{ $currency }}" :selected="old('default_currency', $generalSettings['default_currency'] ?? '') === $currency">
                                        {{ $currency }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                            <flux:select name="default_locale" label="{{ __('Default locale') }}">
                                @foreach ($locales as $locale)
                                    <flux:select.option value="{{ $locale }}" :selected="old('default_locale', $generalSettings['default_locale'] ?? '') === $locale">
                                        {{ strtoupper($locale) }}
                                    </flux:select.option>
                                @endforeach
                            </flux:select>
                        </div>

                        <flux:select name="timezone" label="{{ __('Timezone') }}">
                            @foreach ($timezones as $timezone)
                                <flux:select.option value="{{ $timezone }}" :selected="old('timezone', $generalSettings['timezone'] ?? '') === $timezone">
                                    {{ $timezone }}
                                </flux:select.option>
                            @endforeach
                        </flux:select>
                    </div>

                    <div class="space-y-4">
                        <flux:heading size="lg" level="2">{{ __('Branding') }}</flux:heading>
                        @if (!empty($generalSettings['logo_path']))
                            <div class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-700 dark:bg-zinc-800/60">
                                <img src="{{ Storage::url($generalSettings['logo_path']) }}" alt="{{ __('Current logo') }}" class="h-16 object-contain">
                            </div>
                        @endif
                        <flux:input type="file" name="logo" label="{{ __('Logo') }}" />
                        <flux:button variant="primary" type="submit">{{ __('Save general settings') }}</flux:button>
                    </div>
                </form>
            </div>

            <div class="tab-panel rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900" data-tab="seo">
                <form method="POST" action="{{ route('admin.settings.seo') }}">
                    @csrf

                    <div class="flex items-center justify-between">
                        <div>
                            <flux:heading size="lg" level="2">{{ __('SEO configuration') }}</flux:heading>
                            <flux:text>{{ __('Control metadata for the storefront. Add a route name to target a specific page.') }}</flux:text>
                        </div>
                        <flux:button variant="primary" type="submit">{{ __('Save SEO') }}</flux:button>
                    </div>

                    <input type="hidden" name="slug" value="{{ old('slug', $seoSetting->slug ?? 'global') }}">

                    <div class="grid gap-4 lg:grid-cols-2 mt-6">
                        <flux:input
                            name="title"
                            label="{{ __('SEO title') }}"
                            value="{{ old('title', $seoSetting->title) }}"
                        />
                        <flux:input
                            name="route_name"
                            label="{{ __('Route name (optional)') }}"
                            value="{{ old('route_name', $seoSetting->route_name) }}"
                            placeholder="pages.home"
                        />
                    </div>

                    <div class="mt-4">
                        <flux:textarea
                            name="description"
                            label="{{ __('Description') }}"
                            rows="3"
                        >{{ old('description', $seoSetting->description) }}</flux:textarea>
                    </div>

                    <div class="grid gap-4 mt-4 lg:grid-cols-2">
                        <flux:input
                            name="image"
                            label="{{ __('Default image URL') }}"
                            type="url"
                            value="{{ old('image', $seoSetting->image) }}"
                        />
                        <flux:input
                            name="locale"
                            label="{{ __('Locale') }}"
                            value="{{ old('locale', $seoSetting->locale) }}"
                            placeholder="en_US"
                        />
                    </div>

                    <div class="grid gap-4 mt-4 lg:grid-cols-2">
                        <div>
                            <label class="text-sm font-semibold text-slate-700" for="meta">{{ __('Extra meta (JSON)') }}</label>
                            <flux:textarea
                                name="meta"
                                id="meta"
                                rows="4"
                                placeholder='{"robots": "index,follow"}'
                            >{{ old('meta', $seoSetting->meta ? json_encode($seoSetting->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '') }}</flux:textarea>
                        </div>
                        <div>
                            <label class="text-sm font-semibold text-slate-700" for="json_ld">{{ __('JSON-LD payload') }}</label>
                            <flux:textarea
                                name="json_ld"
                                id="json_ld"
                                rows="4"
                                placeholder='[{"@type": "WebSite", "@id": "https://example.com"}]'
                            >{{ old('json_ld', $seoSetting->json_ld ? json_encode($seoSetting->json_ld, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '') }}</flux:textarea>
                        </div>
                    </div>

                    @php
                        $previewTitle = old('title', $seoSetting->title ?? setting('site_name', config('app.name'), 'general'));
                        $previewDescription = old('description', $seoSetting->description ?? '');
                        $previewUrl = old('canonical_url', setting('site_url', config('app.url'), 'general'));
                    @endphp

                    <div class="mt-6 rounded-xl border border-zinc-200 bg-zinc-50 p-4 text-sm dark:border-zinc-700 dark:bg-zinc-800/60">
                        <div class="text-xs font-semibold uppercase tracking-[0.2em] text-zinc-500">{{ __('Preview') }}</div>
                        <div class="mt-2 text-base font-semibold text-blue-600">{{ $previewTitle }}</div>
                        <div class="text-xs text-emerald-700">{{ $previewUrl }}</div>
                        <div class="mt-1 text-sm text-zinc-600 dark:text-zinc-300">{{ $previewDescription }}</div>
                    </div>
                </form>
            </div>

            <div class="tab-panel rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900" data-tab="payments">
                @php
                    $credentialFields = [
                        'mada' => ['merchant_id', 'secret'],
                        'stcpay' => ['merchant_id', 'secret'],
                        'applepay' => ['merchant_id', 'key_id', 'private_key'],
                        'mock' => [],
                    ];
                @endphp

                <div class="space-y-4">
                    <flux:heading size="lg" level="2">{{ __('Payment gateways') }}</flux:heading>
                    <flux:text>{{ __('Enable providers, manage credentials, and set display order.') }}</flux:text>

                    <div class="grid gap-4">
                        @foreach ($paymentGateways as $gateway)
                            @php
                                $fields = $credentialFields[$gateway['provider']] ?? [];
                            @endphp
                            <form method="POST" action="{{ route('admin.settings.payments', $gateway['provider']) }}" class="rounded-xl border border-zinc-200 bg-zinc-50 p-4 dark:border-zinc-800 dark:bg-zinc-950">
                                @csrf

                                <div class="flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
                                    <div>
                                        <div class="text-sm font-semibold text-zinc-900 dark:text-zinc-50">{{ $gateway['display_name'] }}</div>
                                        <div class="text-xs text-zinc-500">{{ strtoupper($gateway['provider']) }}</div>
                                    </div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <label class="flex items-center gap-2 text-xs font-semibold text-zinc-600">
                                            <input type="checkbox" name="enabled" value="1" @checked(old('enabled', $gateway['enabled']))>
                                            {{ __('Enabled') }}
                                        </label>
                                        <label class="flex items-center gap-2 text-xs font-semibold text-zinc-600">
                                            <input type="checkbox" name="sandbox" value="1" @checked(old('sandbox', $gateway['sandbox']))>
                                            {{ __('Sandbox') }}
                                        </label>
                                        <flux:input name="sort_order" label="{{ __('Sort order') }}" type="number" value="{{ old('sort_order', $gateway['sort_order']) }}" />
                                    </div>
                                </div>

                                <div class="mt-4 grid gap-4 md:grid-cols-2">
                                    <flux:input name="display_name" label="{{ __('Display name') }}" value="{{ old('display_name', $gateway['display_name']) }}" />
                                    <flux:input name="webhook_secret" label="{{ __('Webhook secret') }}" value="{{ old('webhook_secret', $gateway['webhook_secret']) }}" />
                                </div>

                                @if (! empty($fields))
                                    <div class="mt-4 grid gap-4 md:grid-cols-2">
                                        @foreach ($fields as $field)
                                            <flux:input
                                                name="credentials[{{ $field }}]"
                                                label="{{ __(str_replace('_', ' ', \Illuminate\Support\Str::title($field))) }}"
                                                value="{{ old('credentials.' . $field, $gateway['credentials'][$field] ?? '') }}"
                                            />
                                        @endforeach
                                    </div>
                                @endif

                                <div class="mt-4">
                                    <flux:button type="submit" variant="outline">{{ __('Save gateway') }}</flux:button>
                                </div>
                            </form>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="tab-panel rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900" data-tab="translations">
                <flux:heading size="lg" level="2">{{ __('Translations') }}</flux:heading>
                <flux:text>{{ __('Manage custom translation keys and overrides in the translation manager.') }}</flux:text>
            </div>

            <div class="tab-panel rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900" data-tab="reports">
                <flux:heading size="lg" level="2">{{ __('Reports') }}</flux:heading>
                <flux:text>{{ __('Generate sales, product, and customer reports with export options.') }}</flux:text>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const initSettingsTabs = () => {
            const tabs = document.querySelectorAll('[data-tab-target]');
            const panels = document.querySelectorAll('.tab-panel');

            if (!tabs.length || !panels.length) return;

            const setActive = (target) => {
                tabs.forEach((tab) => {
                    const selected = tab.getAttribute('data-tab-target') === target;
                    tab.classList.toggle('bg-slate-900', selected);
                    tab.classList.toggle('text-white', selected);
                    tab.classList.toggle('shadow-lg', selected);
                    tab.classList.toggle('hover:bg-slate-100', !selected);
                    tab.setAttribute('aria-selected', selected ? 'true' : 'false');
                });

                panels.forEach((panel) => {
                    panel.classList.toggle('hidden', panel.getAttribute('data-tab') !== target);
                });
            };

            const defaultTab = tabs[0]?.getAttribute('data-tab-target') ?? 'general';
            setActive(defaultTab);

            tabs.forEach((tab) => {
                tab.addEventListener('click', () => {
                    setActive(tab.getAttribute('data-tab-target'));
                });
            });
        };

        document.addEventListener('DOMContentLoaded', initSettingsTabs);
        document.addEventListener('livewire:navigated', initSettingsTabs);
    </script>
@endpush
