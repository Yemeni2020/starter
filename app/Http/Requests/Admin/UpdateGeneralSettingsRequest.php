<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGeneralSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $locales = config('app.supported_locales', ['en', 'ar']);

        return [
            'site_name' => ['required', 'string', 'max:255'],
            'site_url' => ['required', 'url', 'max:255'],
            'support_email' => ['nullable', 'email', 'max:255'],
            'default_currency' => ['required', 'string', 'max:3'],
            'default_locale' => ['required', Rule::in($locales)],
            'timezone' => ['required', 'string', 'max:64'],
            'logo' => ['nullable', 'file', 'image', 'max:2048'],
        ];
    }
}
