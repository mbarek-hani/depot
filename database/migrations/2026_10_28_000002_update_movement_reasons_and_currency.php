<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('stock_movements')
            ->whereIn('reason', ['purchase', 'donation'])
            ->update(['reason' => 'company']);

        DB::table('stock_movements')
            ->where('reason', 'distribution')
            ->update(['reason' => 'kitchen']);

        DB::table('stock_movements')
            ->whereIn('reason', ['opening', 'correction', 'damage', 'return', 'other'])
            ->update(['reason' => 'other']);

        DB::table('settings')
            ->where('key', 'currency')
            ->where('value', 'ر.س')
            ->update(['value' => 'MAD', 'updated_at' => now()]);
    }

    public function down(): void
    {
        DB::table('stock_movements')
            ->where('reason', 'company')
            ->update(['reason' => 'donation']);

        DB::table('stock_movements')
            ->where('reason', 'kitchen')
            ->update(['reason' => 'distribution']);

        DB::table('stock_movements')
            ->whereIn('reason', ['administration', 'club', 'other'])
            ->update(['reason' => 'other']);
    }
};
