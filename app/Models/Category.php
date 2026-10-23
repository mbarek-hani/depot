<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Category extends Model
{
    use HasFactory;

    public const COLORS = [
        'brand' => 'أصفر',
        'emerald' => 'أخضر',
        'sky' => 'أزرق',
        'violet' => 'بنفسجي',
        'rose' => 'وردي',
        'orange' => 'برتقالي',
        'maroon' => 'عنابي',
    ];

    protected $fillable = [
        'name',
        'color',
        'description',
    ];

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function colorClasses(): string
    {
        return match ($this->color) {
            'emerald' => 'bg-emerald-500',
            'sky' => 'bg-sky-500',
            'violet' => 'bg-violet-500',
            'rose' => 'bg-rose-500',
            'orange' => 'bg-orange-500',
            'maroon' => 'bg-maroon-500',
            default => 'bg-brand-400',
        };
    }
}
