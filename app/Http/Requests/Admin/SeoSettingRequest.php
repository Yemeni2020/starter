<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class SeoSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->is_admin === true;
    }

    public function rules(): array
    {
        return [
            'slug' => ['required', 'string', 'max:100'],
            'route_name' => ['nullable', 'string', 'max:255'],
            'title' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:500'],
            'image' => ['nullable', 'url', 'max:2048'],
            'locale' => ['nullable', 'string', 'max:20'],
            'meta' => ['nullable', 'json'],
            'json_ld' => ['nullable', 'json'],
        ];
    }
}