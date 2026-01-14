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
            if (! Schema::hasColumn('products', 'status')) {
                $table->string('status')->default('ACTIVE')->after('weight_grams');
            }
        });

        if (Schema::hasColumn('products', 'weight_grams')) {
            DB::table('products')->whereNull('weight_grams')->update(['weight_grams' => 0]);
        }

        if (Schema::hasColumn('products', 'status')) {
            DB::table('products')->whereNull('status')->update([
                'status' => 'ACTIVE',
            ]);
        }

        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            if (Schema::hasColumn('products', 'weight_grams')) {
                DB::statement('ALTER TABLE products MODIFY weight_grams INT UNSIGNED NOT NULL DEFAULT 0');
            }
            if (Schema::hasColumn('products', 'category_id')) {
                DB::statement('ALTER TABLE products MODIFY category_id BIGINT UNSIGNED NULL');
            }
        }
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            if (Schema::hasColumn('products', 'category_id')) {
                DB::statement('ALTER TABLE products MODIFY category_id BIGINT UNSIGNED NOT NULL');
            }
            if (Schema::hasColumn('products', 'weight_grams')) {
                DB::statement('ALTER TABLE products MODIFY weight_grams INT UNSIGNED NULL');
            }
        }

        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
