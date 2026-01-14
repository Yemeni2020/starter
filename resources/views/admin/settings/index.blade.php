@extends('admin.layouts.app')

@section('content')
    <div class="flex w-full flex-1 flex-col gap-6">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="space-y-1">
                <flux:heading size="xl" level="1">Settings</flux:heading>
                <flux:text>Configure store preferences, operational rules, and alerts.</flux:text>
            </div>
            <div class="flex flex-wrap gap-3">
                <flux:button variant="outline">Discard</flux:button>
                <flux:button variant="primary" icon="check" icon:variant="outline">Save changes</flux:button>
            </div>
        </div>

        <div class="flex flex-wrap gap-3 rounded-full border border-slate-200 bg-white px-4 py-2 shadow-inner" role="tablist">
            <button type="button" data-tab-target="store" class="tab-toggle rounded-full px-4 py-2 text-sm font-semibold text-slate-800 transition hover:bg-slate-100 focus:outline-none" aria-selected="true">Store</button>
            <button type="button" data-tab-target="shipping" class="tab-toggle rounded-full px-4 py-2 text-sm font-semibold text-slate-800 transition hover:bg-slate-100 focus:outline-none">Shipping</button>
            <button type="button" data-tab-target="operations" class="tab-toggle rounded-full px-4 py-2 text-sm font-semibold text-slate-800 transition hover:bg-slate-100 focus:outline-none">Operations</button>
            <button type="button" data-tab-target="notifications" class="tab-toggle rounded-full px-4 py-2 text-sm font-semibold text-slate-800 transition hover:bg-slate-100 focus:outline-none">Notifications</button>
            <button type="button" data-tab-target="seo" class="tab-toggle rounded-full px-4 py-2 text-sm font-semibold text-slate-800 transition hover:bg-slate-100 focus:outline-none">SEO</button>
        </div>

        <form class="grid gap-6 xl:grid-cols-[2fr_1fr]" id="settingsTabs">
            <div class="flex flex-col gap-6">
                <div class="tab-panel rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900" data-tab="store">
                    <div class="flex flex-col gap-4">
                        <flux:heading size="lg" level="2">Store profile</flux:heading>
                        <flux:input name="store_name" label="Store name" value="Otex Home" />
                        <div class="grid gap-4 md:grid-cols-2">
                            <flux:input name="support_email" label="Support email" type="email" value="support@otex.com" />
                            <flux:input name="phone" label="Support phone" value="+1 (555) 234-9812" />
                        </div>
                        <flux:textarea name="address" label="Business address" rows="4">745 Market Street, Suite 200, San Francisco, CA</flux:textarea>
                        <div class="grid gap-4 md:grid-cols-2">
                            <flux:select name="timezone" label="Timezone">
                                <flux:select.option value="pst" selected>Pacific (PST)</flux:select.option>
                                <flux:select.option value="est">Eastern (EST)</flux:select.option>
                                <flux:select.option value="gmt">GMT</flux:select.option>
                            </flux:select>
                            <flux:select name="currency" label="Currency">
                                <flux:select.option value="usd" selected>USD</flux:select.option>
                                <flux:select.option value="eur">EUR</flux:select.option>
                                <flux:select.option value="gbp">GBP</flux:select.option>
                            </flux:select>
                        </div>
                    </div>
                </div>

                <div class="tab-panel rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900" data-tab="shipping">
                    <div class="flex flex-col gap-4">
                        <flux:heading size="lg" level="2">Shipping defaults</flux:heading>
                        <flux:input name="origin" label="Shipping origin" value="San Francisco, CA" />
                        <div class="grid gap-4 md:grid-cols-2">
                            <flux:input name="shipping_days" label="Processing time (days)" type="number" value="2" />
                            <flux:input name="free_shipping_threshold" label="Free shipping threshold" type="number" value="150" />
                        </div>
                        <flux:select name="carrier" label="Default carrier">
                            <flux:select.option value="ups" selected>UPS</flux:select.option>
                            <flux:select.option value="fedex">FedEx</flux:select.option>
                            <flux:select.option value="dhl">DHL</flux:select.option>
                        </flux:select>
                    </div>
                </div>

                <div class="tab-panel rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900" data-tab="notifications">
                    <div class="flex flex-col gap-4">
                        <flux:heading size="lg" level="2">Notifications</flux:heading>
                        <flux:switch name="notify_orders" label="Order updates" checked />
                        <flux:switch name="notify_stock" label="Low stock alerts" checked />
                        <flux:switch name="notify_reviews" label="New reviews" />
                    </div>
                </div>
            </div>

            <div class="flex flex-col gap-6">
                <div class="tab-panel rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900" data-tab="operations">
                    <div class="flex flex-col gap-4">
                        <flux:heading size="lg" level="2">reCAPTCHA & Social login</flux:heading>
                        <div class="grid gap-4">
                            <div class="rounded-2xl border border-zinc-200 bg-slate-50 p-4">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-semibold text-slate-700">Turn on reCAPTCHA</h3>
                                    <div class="flex items-center gap-3">
                                        <span class="text-xs font-semibold uppercase tracking-[0.3em] text-slate-500">Status</span>
                                        <label class="relative inline-flex items-center cursor-pointer">
                                            <input type="checkbox" class="sr-only peer" checked>
                                            <div class="w-12 h-6 bg-slate-200 rounded-full peer-checked:bg-blue-600 peer-focus:ring-2 peer-focus:ring-blue-500 transition"></div>
                                            <span class="absolute left-1 top-1 h-4 w-4 rounded-full bg-white peer-checked:translate-x-6 transition"></span>
                                        </label>
                                    </div>
                                </div>

                                <div class="mt-4 grid gap-4 md:grid-cols-2">
                                    <flux:input name="recaptcha_site" label="Site Key" placeholder="Enter reCAPTCHA site key" />
                                    <flux:input name="recaptcha_secret" label="Secret Key" placeholder="Enter reCAPTCHA secret key" />
                                </div>

                                <div class="mt-4 space-y-1 text-xs text-slate-500">
                                    <p class="font-semibold text-slate-900">Instructions</p>
                                    <p>1. Create credentials on Google reCAPTCHA portal.</p>
                                    <p>2. Set ReCAPTCHA v2 with checkbox.</p>
                                    <p>3. Add your domain and copy keys here.</p>
                                    <p>4. Paste Site Key + Secret Key and toggle on.</p>
                                </div>
                            </div>
                            <div class="rounded-2xl border border-zinc-200 bg-white p-4">
                                <h3 class="text-sm font-semibold text-slate-700 mb-3">Social media login</h3>
                                <div class="grid gap-4 xl:grid-cols-2">
                                    @foreach (['Google', 'Facebook', 'Apple'] as $provider)
                                        <div class="rounded-2xl border border-slate-100 p-4 bg-slate-50">
                                            <div class="flex items-center justify-between text-sm font-semibold text-slate-900 mb-3">
                                                <span>{{ $provider }}</span>
                                                <span class="text-xs text-slate-400">Callback URI</span>
                                            </div>
                                            <flux:input name="social_{{ strtolower($provider) }}_client_id" label="Client ID" placeholder="Example client id" />
                                            <flux:input name="social_{{ strtolower($provider) }}_secret" label="Client secret" placeholder="Example secret key" />
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div id="seoTab" class="mt-6 rounded-xl border border-zinc-200 bg-white p-6 shadow-xs dark:border-zinc-700 dark:bg-zinc-900 tab-panel" data-tab="seo">
            <form method="POST" action="{{ route('admin.settings.seo') }}">
                @csrf

                <div class="flex items-center justify-between">
                    <div>
                        <flux:heading size="lg" level="2">SEO configuration</flux:heading>
                        <flux:text>Control metadata for the storefront. Add a route name to target a specific page.</flux:text>
                    </div>
                    <flux:button variant="primary" type="submit">Save SEO</flux:button>
                </div>

                @if(session('status'))
                    <div class="mt-4 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-semibold text-emerald-700">
                        {{ session('status') }}
                    </div>
                @endif

                <input type="hidden" name="slug" value="{{ old('slug', $seoSetting->slug ?? 'global') }}">

                <div class="grid gap-4 lg:grid-cols-2 mt-6">
                    <flux:input
                        name="title"
                        label="SEO title"
                        value="{{ old('title', $seoSetting->title) }}"
                    />
                    <flux:input
                        name="route_name"
                        label="Route name (optional)"
                        value="{{ old('route_name', $seoSetting->route_name) }}"
                        placeholder="pages.home"
                    />
                </div>

                <div class="mt-4">
                    <flux:textarea
                        name="description"
                        label="Description"
                        rows="3"
                    >{{ old('description', $seoSetting->description) }}</flux:textarea>
                </div>

                <div class="grid gap-4 mt-4 lg:grid-cols-2">
                    <flux:input
                        name="image"
                        label="Default image URL"
                        type="url"
                        value="{{ old('image', $seoSetting->image) }}"
                    />
                    <flux:input
                        name="locale"
                        label="Locale"
                        value="{{ old('locale', $seoSetting->locale) }}"
                        placeholder="en_US"
                    />
                </div>

                <div class="grid gap-4 mt-4 lg:grid-cols-2">
                    <div>
                        <label class="text-sm font-semibold text-slate-700" for="meta">Extra meta (JSON)</label>
                        <flux:textarea
                            name="meta"
                            id="meta"
                            rows="4"
                            placeholder='{"robots": "index,follow"}'
                        >{{ old('meta', $seoSetting->meta ? json_encode($seoSetting->meta, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '') }}</flux:textarea>
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-slate-700" for="json_ld">JSON-LD payload</label>
                        <flux:textarea
                            name="json_ld"
                            id="json_ld"
                            rows="4"
                            placeholder='[{"@type": "WebSite", "@id": "https://example.com"}]'
                        >{{ old('json_ld', $seoSetting->json_ld ? json_encode($seoSetting->json_ld, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) : '') }}</flux:textarea>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const tabs = document.querySelectorAll('[data-tab-target]');
            const panels = document.querySelectorAll('.tab-panel');

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

            const defaultTab = tabs[0]?.getAttribute('data-tab-target') ?? 'store';
            setActive(defaultTab);

            tabs.forEach((tab) => {
                tab.addEventListener('click', () => {
                    setActive(tab.getAttribute('data-tab-target'));
                });
            });
        });
    </script>
@endpush
