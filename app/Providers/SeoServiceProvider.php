<?php

namespace App\Providers;

use App\Models\SeoSetting;
use App\Services\Seo\SeoManager;
use Illuminate\Database\Schema\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class SeoServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SeoManager::class, function () {
            return new SeoManager(config('seo'));
        });
    }

    public function boot(Request $request): void
    {
        $seo = $this->app->make(SeoManager::class);
        $routeName = optional($request->route())->getName();

        if (! Schema::hasTable('seo_settings')) {
            view()->share('seo', $seo);
            view()->share('seoSetting', null);

            return;
        }

        $seoSetting = $routeName
            ? SeoSetting::forRoute($routeName)->first()
            : null;

        if (! $seoSetting) {
            $seoSetting = SeoSetting::where('slug', 'global')->first();
        }

        $seo->applySeoSetting($seoSetting);

        view()->share('seo', $seo);
        view()->share('seoSetting', $seoSetting);
    }
}
