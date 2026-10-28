<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $seen = [];

        DB::table('users')->orderBy('id')->get(['id', 'name'])->each(function (object $user) use (&$seen): void {
            $base = trim((string) $user->name) ?: 'user-'.$user->id;
            $name = $base;
            $suffix = 2;

            while (isset($seen[mb_strtolower($name)])) {
                $name = $base.' '.$suffix;
                $suffix++;
            }

            $seen[mb_strtolower($name)] = true;

            if ($name !== $user->name) {
                DB::table('users')->where('id', $user->id)->update(['name' => $name]);
            }
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['email']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['is_active']);
            $table->dropConstrainedForeignId('created_by');
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'email',
                'email_verified_at',
                'role',
                'is_active',
                'receive_expiry_alerts',
                'receive_low_stock_alerts',
            ]);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->unique('name');
        });

        Schema::dropIfExists('password_reset_tokens');
    }

    public function down(): void
    {
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['name']);
            $table->string('email')->nullable()->unique()->after('name');
            $table->timestamp('email_verified_at')->nullable()->after('email');
            $table->string('role')->default('user')->index()->after('email_verified_at');
            $table->boolean('is_active')->default(true)->index()->after('role');
            $table->boolean('receive_expiry_alerts')->default(true)->after('theme');
            $table->boolean('receive_low_stock_alerts')->default(true)->after('receive_expiry_alerts');
            $table->foreignId('created_by')->nullable()->after('receive_low_stock_alerts')->constrained('users')->nullOnDelete();
        });
    }
};
