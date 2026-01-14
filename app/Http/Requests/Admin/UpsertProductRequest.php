<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UpsertProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $locales = config('app.supported_locales', ['ar', 'en']);
        $defaultLocale = config('app.locale', 'ar');

        $nameTranslations = $this->input('name_translations', []);
        $slugs = $this->input('slug_translations', []);

        if (empty($slugs[$defaultLocale]) && !empty($nameTranslations[$defaultLocale])) {
            $slugs[$defaultLocale] = Str::slug($nameTranslations[$defaultLocale]);
            $this->merge(['slug_translations' => $slugs]);
        }

        foreach ($locales as $locale) {
            $nameTranslations[$locale] = $nameTranslations[$locale] ?? '';
        }

        $this->merge([
            'name_translations' => $nameTranslations,
        ]);
    }

    public function rules(): array
    {
        $locales = config('app.supported_locales', ['ar', 'en']);
        $defaultLocale = config('app.locale', 'ar');

        $rules = [
            'brand_id' => ['nullable', 'exists:brands,id'],
            'category_ids' => ['nullable', 'array'],
            'category_ids.*' => ['exists:categories,id'],
            'name_translations' => ['required', 'array'],
            "name_translations.{$defaultLocale}" => ['required', 'string', 'max:255'],
            'slug_translations' => ['nullable', 'array'],
            "slug_translations.{$defaultLocale}" => ['nullable', 'string', 'max:255'],
            'summary_translations' => ['nullable', 'array'],
            'description_translations' => ['nullable', 'array'],
            'price' => ['required', 'numeric', 'min:0'],
            'compare_at_price' => ['nullable', 'numeric', 'min:0'],
            'sku' => ['nullable', 'string', 'max:255'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'reserved_stock' => ['nullable', 'integer', 'min:0'],
            'weight_grams' => ['nullable', 'integer', 'min:0'],
            'status' => ['nullable', 'string', 'max:40'],
            'image' => ['nullable', 'string', 'max:2048'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['string', 'max:2048'],
            'images' => ['nullable', 'array'],
            'images.*' => ['string', 'max:2048'],
            'features' => ['nullable', 'array'],
            'features.*' => ['string', 'max:255'],
            'shipping_returns' => ['nullable', 'array'],
            'shipping_returns.*' => ['string', 'max:255'],
            'rating' => ['nullable', 'numeric', 'min:0', 'max:5'],
            'reviews_count' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['sometimes', 'boolean'],
            'options' => ['nullable', 'array'],
            'options.*.code' => ['required', 'string', 'max:64'],
            'options.*.name_translations' => ['nullable', 'array'],
            'options.*.position' => ['nullable', 'integer'],
            'options.*.values' => ['nullable', 'array'],
            'options.*.values.*.value' => ['required_without:options.*.values.*.label_translations', 'string', 'max:255'],
            'options.*.values.*.label_translations' => ['nullable', 'array'],
            'options.*.values.*.swatch_hex' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'variants' => ['nullable', 'array'],
            'variants.*.id' => ['nullable', 'integer', 'exists:product_variants,id'],
            'variants.*.sku' => ['required', 'string', 'max:255'],
            'variants.*.gtin' => ['nullable', 'string', 'max:64'],
            'variants.*.mpn' => ['nullable', 'string', 'max:64'],
            'variants.*.has_sensor' => ['sometimes', 'boolean'],
            'variants.*.is_active' => ['sometimes', 'boolean'],
            'variants.*.currency' => ['nullable', 'string', 'size:3'],
            'variants.*.price_cents' => ['required', 'integer', 'min:0'],
            'variants.*.compare_at_cents' => ['nullable', 'integer', 'min:0'],
            'variants.*.cost_cents' => ['nullable', 'integer', 'min:0'],
            'variants.*.sale_cents' => ['nullable', 'integer', 'min:0'],
            'variants.*.sale_starts_at' => ['nullable', 'date'],
            'variants.*.sale_ends_at' => ['nullable', 'date'],
            'variants.*.weight_grams' => ['nullable', 'integer', 'min:0'],
            'variants.*.length_mm' => ['nullable', 'integer', 'min:0'],
            'variants.*.width_mm' => ['nullable', 'integer', 'min:0'],
            'variants.*.height_mm' => ['nullable', 'integer', 'min:0'],
            'variants.*.track_inventory' => ['sometimes', 'boolean'],
            'variants.*.allow_backorder' => ['sometimes', 'boolean'],
            'variants.*.low_stock_threshold' => ['nullable', 'integer', 'min:0'],
            'variants.*.metadata' => ['nullable', 'array'],
            'variants.*.option_values' => ['nullable', 'array'],
            'variants.*.option_values.*.option_value_id' => ['nullable', 'integer', 'exists:product_option_values,id'],
            'variants.*.option_values.*.option_code' => ['nullable', 'string'],
            'variants.*.option_values.*.value' => ['nullable', 'string'],
            'variants.*.inventory' => ['nullable', 'array'],
            'variants.*.inventory.*.location_code' => ['required', 'string', 'exists:inventory_locations,code'],
            'variants.*.inventory.*.on_hand' => ['nullable', 'integer', 'min:0'],
            'variants.*.inventory.*.reserved' => ['nullable', 'integer', 'min:0'],
            'media' => ['nullable', 'array'],
            'media.*.url' => ['required', 'string', 'max:2048'],
            'media.*.type' => ['nullable', 'in:image,video,model_3d,IMAGE,VIDEO,MODEL_3D'],
            'media.*.alt_text' => ['nullable', 'string', 'max:255'],
            'media.*.position' => ['nullable', 'integer', 'min:0'],
            'media.*.is_primary' => ['sometimes', 'boolean'],
            'media.*.option_value_id' => ['nullable', 'integer', 'exists:product_option_values,id'],
            'media.*.option_code' => ['nullable', 'string'],
            'media.*.value' => ['nullable', 'string'],
            'media.*.variant_sku' => ['nullable', 'string'],
            'attributes' => ['nullable', 'array'],
            'attributes.*.definition_key' => ['required', 'string', 'exists:attribute_definitions,key'],
            'attributes.*.variant_sku' => ['nullable', 'string'],
            'attributes.*.value' => ['required'],
        ];

        foreach ($locales as $locale) {
            $rules["name_translations.{$locale}"] = $rules["name_translations.{$locale}"] ?? ['nullable', 'string', 'max:255'];
            $rules["slug_translations.{$locale}"] = $rules["slug_translations.{$locale}"] ?? ['nullable', 'string', 'max:255'];
            $rules["summary_translations.{$locale}"] = ['nullable', 'string', 'max:500'];
            $rules["description_translations.{$locale}"] = ['nullable', 'string'];
        }

        return $rules;
    }
}
