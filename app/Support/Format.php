<?php

namespace App\Support;

use Illuminate\Support\Carbon;

class Format
{
    public static function quantity(mixed $value): string
    {
        $number = (float) $value;

        return number_format($number, floor($number) === $number ? 0 : 2, '.', ',');
    }

    public static function money(mixed $value): string
    {
        return number_format((float) $value, 2, '.', ',').' '.Settings::currency();
    }

    public static function date(?Carbon $date): string
    {
        return $date ? $date->format('Y/m/d') : '—';
    }

    public static function dateTime(?Carbon $date): string
    {
        return $date ? $date->format('Y/m/d - H:i') : '—';
    }

    public static function longDate(?Carbon $date): string
    {
        return $date ? $date->translatedFormat('j F Y') : '—';
    }
}
