<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        DB::table('products')->select(['id', 'image', 'gallery', 'images'])->orderBy('id')->get()->each(function ($product) use ($now) {
            $candidates = [];

            if ($product->images) {
                $decoded = json_decode($product->images, true);
                if (is_array($decoded)) {
                    $candidates = array_merge($candidates, $decoded);
                }
            }

            if ($product->gallery) {
                $decoded = json_decode($product->gallery, true);
                if (is_array($decoded)) {
                    $candidates = array_merge($candidates, $decoded);
                }
            }

            if ($product->image) {
                $candidates[] = $product->image;
            }

            $candidates = array_values(array_filter(array_unique($candidates)));

            if (empty($candidates)) {
                return;
            }

            foreach ($candidates as $index => $url) {
                DB::table('media_assets')->insert([
                    'product_id' => $product->id,
                    'url' => $url,
                    'type' => 'image',
                    'position' => $index,
                    'is_primary' => $index === 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });
    }

    public function down(): void
    {
        // Nothing to clean up; table drops roll back all entries.
    }
};
