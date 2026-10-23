<?php

namespace App\Enums;

enum StockStatus: string
{
    case Available = 'available';
    case Low = 'low';
    case Out = 'out';

    public function label(): string
    {
        return match ($this) {
            self::Available => 'متوفر',
            self::Low => 'مخزون منخفض',
            self::Out => 'نفد المخزون',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Available => 'success',
            self::Low => 'warning',
            self::Out => 'danger',
        };
    }
}
