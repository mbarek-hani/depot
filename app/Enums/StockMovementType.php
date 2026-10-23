<?php

namespace App\Enums;

enum StockMovementType: string
{
    case In = 'in';
    case Out = 'out';

    public function label(): string
    {
        return match ($this) {
            self::In => 'إدخال',
            self::Out => 'إخراج',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::In => 'success',
            self::Out => 'danger',
        };
    }

    public function sign(): string
    {
        return match ($this) {
            self::In => '+',
            self::Out => '-',
        };
    }
}
