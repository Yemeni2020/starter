<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        $nameEn = $this->faker->unique()->words(2, true);
        $nameAr = $this->randomArabicCategory();
        $slugEn = Str::slug($nameEn);
        $slugAr = $this->slugifyArabic($nameAr);
        $suffix = $this->faker->unique()->numberBetween(100, 9999);
        $slugEn = "{$slugEn}-{$suffix}";
        $slugAr = "{$slugAr}-{$suffix}";

        return [
            'name' => Str::title($nameEn),
            'slug' => $slugEn,
            'name_translations' => [
                'en' => Str::title($nameEn),
                'ar' => $nameAr,
            ],
            'slug_translations' => [
                'en' => $slugEn,
                'ar' => $slugAr,
            ],
            'is_active' => true,
        ];
    }

    private function randomArabicCategory(): string
    {
        $items = [
            'إلكترونيات',
            'أجهزة منزلية',
            'إكسسوارات',
            'معدات رياضية',
            'أثاث',
            'إضاءة',
            'مستلزمات مكتبية',
            'عناية شخصية',
        ];

        return $this->faker->randomElement($items);
    }

    private function slugifyArabic(string $value): string
    {
        $slug = Str::of($value)
            ->lower()
            ->replace(' ', '-')
            ->replaceMatches('/[^\pL\pN\-]+/u', '')
            ->__toString();

        return $slug !== '' ? $slug : Str::random(6);
    }
}
