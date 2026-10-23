<?php

namespace App\Enums;

enum ExpiryStatus: string
{
    case None = 'none';
    case Valid = 'valid';
    case Expiring = 'expiring';
    case Expired = 'expired';

    public function label(): string
    {
        return match ($this) {
            self::None => 'بدون تاريخ انتهاء',
            self::Valid => 'صالح',
            self::Expiring => 'قريب الانتهاء',
            self::Expired => 'منتهي الصلاحية',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::None => 'neutral',
            self::Valid => 'success',
            self::Expiring => 'warning',
            self::Expired => 'danger',
        };
    }
}
