<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('products', 'deleted_at')) {
            $productIds = DB::table('products')->whereNotNull('deleted_at')->pluck('id');

            if ($productIds->isNotEmpty()) {
                DB::table('stock_movements')->whereIn('product_id', $productIds)->delete();
                DB::table('products')->whereIn('id', $productIds)->delete();
            }

            Schema::table('products', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }

        if (Schema::hasColumn('categories', 'deleted_at')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->dropSoftDeletes();
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasColumn('products', 'deleted_at')) {
            Schema::table('products', function (Blueprint $table) {
                $table->softDeletes();
            });
        }

        if (! Schema::hasColumn('categories', 'deleted_at')) {
            Schema::table('categories', function (Blueprint $table) {
                $table->softDeletes();
            });
        }
    }
};
