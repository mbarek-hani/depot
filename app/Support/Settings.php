<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Settings
{
    public const DEFAULTS = [
        'association_name' => 'مستودع جمعية تحدي الإعاقة',
        'association_phone' => '',
        'association_email' => '',
        'low_stock_threshold' => '10',
        'expiry_warning_days' => '30',
        'currency' => 'MAD',
        'report_footer' => 'مستودع جمعية تحدي الإعاقة — تقرير داخلي',
    ];

    protected static ?array $cache = null;

    public static function all(): array
    {
        if (self::$cache !== null) {
            return self::$cache;
        }

        if (! Schema::hasTable('settings')) {
            return self::$cache = self::DEFAULTS;
        }

        $stored = DB::table('settings')->pluck('value', 'key')->all();

        return self::$cache = array_merge(self::DEFAULTS, $stored);
    }

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::all()[$key] ?? $default ?? self::DEFAULTS[$key] ?? null;
    }

    public static function set(string $key, mixed $value): void
    {
        DB::table('settings')->updateOrInsert(
            ['key' => $key],
            ['value' => is_scalar($value) || $value === null ? (string) $value : json_encode($value), 'updated_at' => now(), 'created_at' => now()],
        );

        self::flush();
    }

    public static function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            self::set($key, $value);
        }
    }

    public static function lowStockThreshold(): float
    {
        return max(0, (float) self::get('low_stock_threshold'));
    }

    public static function expiryWarningDays(): int
    {
        return max(1, (int) self::get('expiry_warning_days'));
    }

    public static function associationName(): string
    {
        return (string) self::get('association_name');
    }

    public static function currency(): string
    {
        return (string) self::get('currency');
    }

    public static function flush(): void
    {
        self::$cache = null;
    }
}
