<?php

namespace App\Enums;

enum MovementReason: string
{
    case Purchase = 'purchase';
    case Opening = 'opening';
    case Donation = 'donation';
    case Distribution = 'distribution';
    case Damage = 'damage';
    case Correction = 'correction';
    case Return = 'return';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Purchase => 'شراء',
            self::Opening => 'رصيد افتتاحي',
            self::Donation => 'تبرع',
            self::Distribution => 'توزيع',
            self::Damage => 'تلف',
            self::Correction => 'تصحيح جرد',
            self::Return => 'إرجاع',
            self::Other => 'أخرى',
        };
    }

    public static function forType(StockMovementType $type): array
    {
        return match ($type) {
            StockMovementType::In => [self::Purchase, self::Opening, self::Donation, self::Return, self::Correction, self::Other],
            StockMovementType::Out => [self::Distribution, self::Damage, self::Correction, self::Return, self::Other],
        };
    }
}
