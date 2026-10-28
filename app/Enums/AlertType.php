<?php

namespace App\Enums;

enum AlertType: string
{
    case LowStock = 'low_stock';
    case Expiry = 'expiry';

    public function label(): string
    {
        return match ($this) {
            self::LowStock => 'تنبيه مخزون',
            self::Expiry => 'تنبيه صلاحية',
        };
    }
}
