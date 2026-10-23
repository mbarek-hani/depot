<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->index()->after('email');
            $table->boolean('is_active')->default(true)->index()->after('email_verified_at');
            $table->string('theme')->default('system')->after('is_active');
            $table->boolean('receive_expiry_alerts')->default(false)->after('theme');
            $table->boolean('receive_low_stock_alerts')->default(false)->after('receive_expiry_alerts');
            $table->foreignId('created_by')->nullable()->after('receive_low_stock_alerts')->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
            $table->dropColumn([
                'role',
                'is_active',
                'theme',
                'receive_expiry_alerts',
                'receive_low_stock_alerts',
            ]);
        });
    }
};
