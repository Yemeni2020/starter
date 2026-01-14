<?php

namespace App\Services\Security;

use App\Models\SecuritySetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;

class SecuritySettings
{
    protected ?SecuritySetting $setting = null;

    public function __construct()
    {
        if (! Schema::hasTable('security_settings')) {
            return;
        }

        $this->setting = SecuritySetting::firstWhere('slug', 'global');
    }

    public function get(): ?SecuritySetting
    {
        return $this->setting;
    }

    public function recaptchaEnabled(): bool
    {
        return (bool) ($this->setting?->recaptcha_enabled);
    }

    public function recaptchaSiteKey(): ?string
    {
        return $this->setting?->recaptcha_site_key;
    }

    public function recaptchaSecretKey(): ?string
    {
        return $this->setting?->recaptcha_secret_key;
    }

    public function socialConfig(string $provider): ?array
    {
        if (! $this->setting?->social) {
            return null;
        }

        return $this->setting->social[strtolower($provider)] ?? null;
    }

    public function socials(): array
    {
        return $this->setting?->social ?? [];
    }

    public function verifyRecaptcha(?string $token, ?string $remoteIp = null): bool
    {
        if (! $this->recaptchaEnabled() || ! $token || ! $this->recaptchaSecretKey()) {
            return true;
        }

        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => $this->recaptchaSecretKey(),
            'response' => $token,
            'remoteip' => $remoteIp,
        ]);

        return $response->ok() && $response->json('success') === true;
    }
}
