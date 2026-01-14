<?php

namespace Database\Seeders;

use App\Models\Color;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ColorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $colors = [
            ['name' => 'Red', 'hex' => '#FF0000'],
            ['name' => 'Green', 'hex' => '#00FF00'],
            ['name' => 'Blue', 'hex' => '#0000FF'],
            ['name' => 'Yellow', 'hex' => '#FFFF00'],
            ['name' => 'Cyan', 'hex' => '#00FFFF'],
            ['name' => 'Magenta', 'hex' => '#FF00FF'],
            ['name' => 'Black', 'hex' => '#000000'],
            ['name' => 'White', 'hex' => '#FFFFFF'],
            ['name' => 'Gray', 'hex' => '#808080'],
            ['name' => 'Orange', 'hex' => '#FFA500'],
            ['name' => 'Purple', 'hex' => '#800080'],
            ['name' => 'Pink', 'hex' => '#FFC0CB'],
            ['name' => 'Brown', 'hex' => '#A52A2A'],
            ['name' => 'Teal', 'hex' => '#008080'],
            ['name' => 'Navy', 'hex' => '#000080'],
            ['name' => 'Lime', 'hex' => '#00FF00'],
            ['name' => 'Indigo', 'hex' => '#4B0082'],
            ['name' => 'Gold', 'hex' => '#FFD700'],
            ['name' => 'Silver', 'hex' => '#C0C0C0'],
            ['name' => 'Maroon', 'hex' => '#800000'],
        ];

        foreach ($colors as $color) {
            $slug = Str::slug($color['name']);
            Color::updateOrCreate(
                ['slug' => $slug],
                [
                    'name' => $color['name'],
                    'hex' => $color['hex'],
                    'slug' => $slug,
                ]
            );
        }
    }
}
