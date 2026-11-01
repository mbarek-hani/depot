<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->date('expiry_date')->nullable()->after('reason');
            $table->decimal('quantity_remaining', 12, 2)->nullable()->after('quantity');
            $table->index(['expiry_date']);
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->string('reason', 30)->nullable()->change();
        });

        $now = now();

        DB::table('products')
            ->select(['id', 'quantity', 'expiry_date'])
            ->orderBy('id')
            ->each(function (object $product) use ($now) {
                $batches = DB::table('stock_movements')
                    ->where('product_id', $product->id)
                    ->where('type', 'in')
                    ->orderBy('occurred_at')
                    ->orderBy('id')
                    ->get(['id', 'quantity'])
                    ->map(fn (object $batch) => (object) [
                        'id' => $batch->id,
                        'quantity' => (float) $batch->quantity,
                        'remaining' => (float) $batch->quantity,
                    ]);

                DB::table('stock_movements')
                    ->where('product_id', $product->id)
                    ->where('type', 'in')
                    ->update(['expiry_date' => $product->expiry_date]);

                $out = (float) DB::table('stock_movements')
                    ->where('product_id', $product->id)
                    ->where('type', 'out')
                    ->sum('quantity');

                $this->consume($batches, $out);

                $remaining = round((float) $batches->sum('remaining'), 2);
                $target = round((float) $product->quantity, 2);

                if ($remaining > $target) {
                    $this->consume($batches, $remaining - $target);
                } elseif ($remaining < $target) {
                    $difference = round($target - $remaining, 2);

                    DB::table('stock_movements')->insert([
                        'product_id' => $product->id,
                        'user_id' => DB::table('users')->value('id'),
                        'type' => 'in',
                        'reason' => 'other',
                        'expiry_date' => $product->expiry_date,
                        'quantity' => $difference,
                        'quantity_remaining' => $difference,
                        'quantity_before' => $remaining,
                        'quantity_after' => $target,
                        'note' => 'تسوية الرصيد عند نقل الصلاحية إلى دفعات المخزون',
                        'occurred_at' => $now,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                foreach ($batches as $batch) {
                    DB::table('stock_movements')
                        ->where('id', $batch->id)
                        ->update(['quantity_remaining' => round($batch->remaining, 2)]);
                }
            });

        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['expiry_date']);
            $table->dropColumn('expiry_date');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->date('expiry_date')->nullable()->after('low_stock_threshold');
            $table->index(['expiry_date']);
        });

        DB::table('products')->select('id')->orderBy('id')->each(function (object $product) {
            $expiry = DB::table('stock_movements')
                ->where('product_id', $product->id)
                ->where('type', 'in')
                ->where('quantity_remaining', '>', 0)
                ->whereNotNull('expiry_date')
                ->orderBy('expiry_date')
                ->value('expiry_date');

            if ($expiry) {
                DB::table('products')->where('id', $product->id)->update(['expiry_date' => $expiry]);
            }
        });

        DB::table('stock_movements')->whereNull('reason')->update(['reason' => 'other']);

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->string('reason', 30)->nullable(false)->change();
            $table->dropIndex(['expiry_date']);
            $table->dropColumn(['expiry_date', 'quantity_remaining']);
        });
    }

    protected function consume($batches, float $quantity): void
    {
        foreach ($batches as $batch) {
            if ($quantity <= 0) {
                return;
            }

            $taken = min($batch->remaining, $quantity);
            $batch->remaining = round($batch->remaining - $taken, 2);
            $quantity = round($quantity - $taken, 2);
        }
    }
};
