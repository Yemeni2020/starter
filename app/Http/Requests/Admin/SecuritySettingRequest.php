<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SecuritySettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    public function rules(): array
    {
        return [
            'recaptcha_enabled' => ['nullable', 'boolean'],
            'recaptcha_site_key' => ['nullable', 'string', 'max:255'],
            'recaptcha_secret_key' => ['nullable', 'string', 'max:255'],
            'social.google.client_id' => ['nullable', 'string', 'max:255'],
            'social.google.client_secret' => ['nullable', 'string', 'max:255'],
            'social.facebook.client_id' => ['nullable', 'string', 'max:255'],
            'social.facebook.client_secret' => ['nullable', 'string', 'max:255'],
            'social.apple.client_id' => ['nullable', 'string', 'max:255'],
            'social.apple.client_secret' => ['nullable', 'string', 'max:255'],
        ];
    }
}