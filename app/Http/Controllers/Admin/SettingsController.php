<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SecuritySettingRequest;
use App\Http\Requests\Admin\SeoSettingRequest;
use App\Models\SecuritySetting;
use App\Models\SeoSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function index(): View
    {
        $seoSetting = SeoSetting::firstOrNew(['slug' => 'global']);

        if (! $seoSetting->exists) {
            $seoSetting->fill([
                'title' => config('seo.site_title'),
                'description' => config('seo.site_description'),
                'image' => config('seo.default_image'),
                'locale' => config('seo.locale'),
            ]);
        }

        $securitySetting = SecuritySetting::firstOrNew(['slug' => 'global']);

        return view('admin.settings.index', [
            'seoSetting' => $seoSetting,
            'securitySetting' => $securitySetting,
        ]);
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

        return back()->with('status', 'SEO settings saved.');
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

        return back()->with('status', 'Security settings saved.');
    }
}
