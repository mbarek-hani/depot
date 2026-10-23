<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case User = 'user';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'مدير النظام',
            self::User => 'مستخدم',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Admin => 'brand',
            self::User => 'neutral',
        };
    }
}
