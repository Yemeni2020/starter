<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SecuritySettingRequest;
use App\Http\Requests\Admin\SeoSettingRequest;
use App\Http\Requests\Admin\UpdateGeneralSettingsRequest;
use App\Models\PaymentGatewaySetting;
use App\Models\SecuritySetting;
use App\Models\SeoSetting;
use App\Services\Settings\SettingsManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(SettingsManager $settings): View
    {
        $seoSetting = SeoSetting::firstOrNew(['slug' => 'global']);

        if (! $seoSetting->exists) {
            $seoSetting->fill([
                'title' => config('seo.site_title', config('app.name')),
                'description' => config('seo.site_description', ''),
                'image' => config('seo.default_image', null),
                'locale' => config('seo.locale', 'en_US'),
            ]);
        }

        $securitySetting = SecuritySetting::firstOrNew(['slug' => 'global']);

        $generalSettings = array_merge([
            'site_name' => config('app.name'),
            'site_url' => config('app.url'),
            'support_email' => config('mail.from.address'),
            'default_currency' => config('store.currency', 'USD'),
            'default_locale' => config('app.locale', 'en'),
            'timezone' => config('app.timezone', 'UTC'),
            'logo_path' => null,
        ], $settings->group('general'));

        $providerKeys = array_keys(config('payments.providers', []));
        if (! in_array('applepay', $providerKeys, true)) {
            $providerKeys[] = 'applepay';
        }

        $paymentSettings = collect();
        if (Schema::hasTable('payment_gateway_settings')) {
            $paymentSettings = PaymentGatewaySetting::query()
                ->whereIn('provider', $providerKeys)
                ->get()
                ->keyBy('provider');
        }

        $paymentGateways = collect($providerKeys)->map(function (string $provider) use ($paymentSettings) {
            $setting = $paymentSettings->get($provider);

            return [
                'provider' => $provider,
                'display_name' => $setting?->display_name ?? Str::title(str_replace('_', ' ', $provider)),
                'enabled' => $setting?->enabled ?? (bool) config("payments.providers.{$provider}.enabled", false),
                'sandbox' => $setting?->sandbox ?? true,
                'sort_order' => $setting?->sort_order ?? 0,
                'credentials' => $setting?->credentials ?? [],
                'webhook_secret' => $setting?->webhook_secret ?? null,
            ];
        })->values()->all();

        return view('admin.settings.index', [
            'seoSetting' => $seoSetting,
            'securitySetting' => $securitySetting,
            'generalSettings' => $generalSettings,
            'paymentGateways' => $paymentGateways,
        ]);
    }

    public function updateGeneral(UpdateGeneralSettingsRequest $request, SettingsManager $settings): RedirectResponse
    {
        $data = $request->validated();

        $payload = [
            'site_name' => $data['site_name'],
            'site_url' => $data['site_url'],
            'support_email' => $data['support_email'] ?? null,
            'default_currency' => strtoupper($data['default_currency']),
            'default_locale' => $data['default_locale'],
            'timezone' => $data['timezone'],
        ];

        if ($request->hasFile('logo')) {
            $payload['logo_path'] = Storage::disk('public')->putFile('settings', $request->file('logo'));
        }

        $settings->setGroup('general', $payload);

        return back()->with('status', __('General settings saved.'));
    }

    public function updateSeo(SeoSettingRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $meta = $data['meta'] ? json_decode($data['meta'], true) : [];
        $jsonLd = $data['json_ld'] ? json_decode($data['json_ld'], true) : [];

        SeoSetting::updateOrCreate(
            ['slug' => $data['slug']],
            [
                'route_name' => $data['route_name'] ?? null,
                'title' => $data['title'] ?? null,
                'description' => $data['description'] ?? null,
                'image' => $data['image'] ?? null,
                'locale' => $data['locale'] ?? null,
                'meta' => $meta ?: null,
                'json_ld' => $jsonLd ?: null,
            ]
        );

        return back()->with('status', __('SEO settings saved.'));

    }

    public function updateSecurity(SecuritySettingRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $social = [
            'google' => [
                'client_id' => $validated['social']['google']['client_id'] ?? null,
                'client_secret' => $validated['social']['google']['client_secret'] ?? null,
            ],
            'facebook' => [
                'client_id' => $validated['social']['facebook']['client_id'] ?? null,
                'client_secret' => $validated['social']['facebook']['client_secret'] ?? null,
            ],
            'apple' => [
                'client_id' => $validated['social']['apple']['client_id'] ?? null,
                'client_secret' => $validated['social']['apple']['client_secret'] ?? null,
            ],
        ];

        SecuritySetting::updateOrCreate(
            ['slug' => $validated['slug'] ?? 'global'],
            [
                'recaptcha_enabled' => $request->boolean('recaptcha_enabled'),
                'recaptcha_site_key' => $validated['recaptcha_site_key'] ?? null,
                'recaptcha_secret_key' => $validated['recaptcha_secret_key'] ?? null,
                'social' => $social,
            ]
        );

        return back()->with('status', __('Security settings saved.'));

    }
}
