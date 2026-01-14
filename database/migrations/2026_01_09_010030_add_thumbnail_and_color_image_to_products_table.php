<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'thumbnail')) {
                $table->string('thumbnail')->nullable()->after('gallery');
            }

            if (!Schema::hasColumn('products', 'color_image')) {
                $table->string('color_image')->nullable()->after('thumbnail');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'color_image')) {
                $table->dropColumn('color_image');
            }

            if (Schema::hasColumn('products', 'thumbnail')) {
                $table->dropColumn('thumbnail');
            }
        });
    }
};
