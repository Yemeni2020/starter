<?php

namespace Database\Seeders;

use App\Models\User;
use Database\Seeders\ColorSeeder;
use Database\Seeders\AttributeDefinitionSeeder;
use Database\Seeders\DemoCatalogSeeder;
use Database\Seeders\ProductSeeder;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::firstOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Demo Customer',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

        $this->call([
            ColorSeeder::class,
            AttributeDefinitionSeeder::class,
            ProductSeeder::class,
        ]);

        if (app()->environment(['local', 'development'])) {
            $this->call(DemoCatalogSeeder::class);
        }
    }
}
