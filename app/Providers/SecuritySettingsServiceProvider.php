<?php

namespace App\Providers;

use App\Services\Security\SecuritySettings;
use Illuminate\Support\ServiceProvider;

class SecuritySettingsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(SecuritySettings::class, function () {
            return new SecuritySettings();
        });
    }

    public function boot(SecuritySettings $securitySettings): void
    {
        view()->share('securitySetting', $securitySettings->get());
        view()->share('securitySettings', $securitySettings);

        foreach ($securitySettings->socials() as $provider => $config) {
            if (empty($config['client_id'])) {
                continue;
            }

            config([
                "services.{$provider}" => array_filter(array_merge($config, [
                    'redirect' => url("/auth/{$provider}/callback"),
                ])),
            ]);
        }
    }
}
