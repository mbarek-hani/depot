<?php

namespace App\Enums;

enum MovementReason: string
{
    case Company = 'company';
    case Kitchen = 'kitchen';
    case Administration = 'administration';
    case Club = 'club';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Company => 'الشركة',
            self::Kitchen => 'المطبخ',
            self::Administration => 'الإدارة',
            self::Club => 'النادي',
            self::Other => 'أخرى',
        };
    }
}
