<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (! Schema::hasColumn('categories', 'name_translations')) {
                $table->json('name_translations')->nullable()->after('name');
            }
            if (! Schema::hasColumn('categories', 'slug_translations')) {
                $table->json('slug_translations')->nullable()->after('slug');
            }
        });

        if (Schema::hasColumn('categories', 'name_translations') && Schema::hasColumn('categories', 'slug_translations')) {
            DB::table('categories')->orderBy('id')->get()->each(function ($category) {
                $name = $category->name ?? '';
                $slug = $category->slug ?? '';

                DB::table('categories')->where('id', $category->id)->update([
                    'name_translations' => json_encode([
                        'ar' => $name,
                        'en' => $name,
                    ], JSON_UNESCAPED_UNICODE),
                    'slug_translations' => json_encode([
                        'ar' => $slug,
                        'en' => $slug,
                    ], JSON_UNESCAPED_UNICODE),
                ]);
            });
        }
    }

    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            if (Schema::hasColumn('categories', 'slug_translations')) {
                $table->dropColumn('slug_translations');
            }
            if (Schema::hasColumn('categories', 'name_translations')) {
                $table->dropColumn('name_translations');
            }
        });
    }
};
