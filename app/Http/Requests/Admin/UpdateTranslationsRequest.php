<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTranslationsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) ($this->user()?->is_admin);
    }

    public function rules(): array
    {
        return [
            'translations' => ['required', 'array'],
            'translations.*' => ['array'],
            'translations.*.*' => ['nullable', 'string'],
        ];
    }
}
