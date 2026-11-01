<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 10);
            $table->string('reason', 30)->nullable();
            $table->date('expiry_date')->nullable();
            $table->decimal('quantity', 12, 2);
            $table->decimal('quantity_remaining', 12, 2)->nullable();
            $table->decimal('quantity_before', 12, 2);
            $table->decimal('quantity_after', 12, 2);
            $table->text('note')->nullable();
            $table->dateTime('occurred_at');
            $table->timestamps();

            $table->index(['product_id', 'occurred_at']);
            $table->index(['type', 'occurred_at']);
            $table->index(['expiry_date']);
            $table->index(['product_id', 'type', 'quantity_remaining']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stock_movements');
    }
};
