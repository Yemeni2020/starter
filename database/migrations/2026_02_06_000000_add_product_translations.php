<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->json('name_translations')->nullable()->after('name');
            $table->json('slug_translations')->nullable()->after('slug');
            $table->json('summary_translations')->nullable()->after('summary');
            $table->json('description_translations')->nullable()->after('description');
            $table->json('seo_title_translations')->nullable()->after('description_translations');
            $table->json('seo_description_translations')->nullable()->after('seo_title_translations');
            $table->json('seo_keywords_translations')->nullable()->after('seo_description_translations');
        });

        DB::table('products')->orderBy('id')->get()->each(function ($product) {
            $name = $product->name ?? '';
            $slug = $product->slug ?? '';
            $summary = $product->summary ?? '';
            $description = $product->description ?? '';

            DB::table('products')->where('id', $product->id)->update([
                'name_translations' => json_encode([
                    'ar' => $name,
                    'en' => $name,
                ], JSON_UNESCAPED_UNICODE),
                'slug_translations' => json_encode([
                    'ar' => $slug,
                    'en' => $slug,
                ], JSON_UNESCAPED_UNICODE),
                'summary_translations' => json_encode([
                    'ar' => $summary,
                    'en' => $summary,
                ], JSON_UNESCAPED_UNICODE),
                'description_translations' => json_encode([
                    'ar' => $description,
                    'en' => $description,
                ], JSON_UNESCAPED_UNICODE),
                'seo_title_translations' => json_encode(['ar' => null, 'en' => null]),
                'seo_description_translations' => json_encode(['ar' => null, 'en' => null]),
                'seo_keywords_translations' => json_encode(['ar' => null, 'en' => null]),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'name_translations',
                'slug_translations',
                'summary_translations',
                'description_translations',
                'seo_title_translations',
                'seo_description_translations',
                'seo_keywords_translations',
            ]);
        });
    }
};
