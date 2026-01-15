<?php

namespace App\Providers;

use App\Services\Translations\TranslationRepository;
use App\Translations\DatabaseTranslationLoader;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('Support/helpers.php');
        $this->app->extend('translation.loader', function ($loader, $app) {
            return new DatabaseTranslationLoader(
                $app['files'],
                $app['path.lang'],
                [$app['path.lang'].'/vendor'],
                $app->make(TranslationRepository::class)
            );
        });
        
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureViews();
    }

    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null
        );
    }

    protected function configureViews(): void
    {
        View::addLocation(resource_path('views/admin'));
        Blade::anonymousComponentPath(resource_path('views/admin/components'));
        Blade::anonymousComponentPath(resource_path('views/admin/flux'), 'flux');
        config(['livewire.view_path' => resource_path('views/admin/livewire')]);
    }
}
