<?php

namespace App\Models;

use App\Enums\ExpiryStatus;
use App\Enums\StockStatus;
use App\Support\Settings;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'category_id',
        'name',
        'sku',
        'unit',
        'quantity',
        'low_stock_threshold',
        'expiry_date',
        'cost_price',
        'sale_price',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'decimal:2',
            'low_stock_threshold' => 'decimal:2',
            'cost_price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'expiry_date' => 'date',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Product $product) {
            if (blank($product->sku)) {
                $product->sku = self::generateSku();
            }
        });
    }

    public static function generateSku(): string
    {
        $sequence = (int) self::withTrashed()->max('id') + 1;

        do {
            $sku = 'PRD-'.str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
            $sequence++;
        } while (self::withTrashed()->where('sku', $sku)->exists());

        return $sku;
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function lowStockThresholdValue(): float
    {
        return (float) ($this->low_stock_threshold ?? Settings::lowStockThreshold());
    }

    public function stockStatus(): StockStatus
    {
        if ((float) $this->quantity <= 0) {
            return StockStatus::Out;
        }

        if ((float) $this->quantity <= $this->lowStockThresholdValue()) {
            return StockStatus::Low;
        }

        return StockStatus::Available;
    }

    public function expiryStatus(): ExpiryStatus
    {
        if (! $this->expiry_date) {
            return ExpiryStatus::None;
        }

        if ($this->expiry_date->lt(today())) {
            return ExpiryStatus::Expired;
        }

        if ($this->expiry_date->lte(today()->addDays(Settings::expiryWarningDays()))) {
            return ExpiryStatus::Expiring;
        }

        return ExpiryStatus::Valid;
    }

    public function daysUntilExpiry(): ?int
    {
        if (! $this->expiry_date) {
            return null;
        }

        return (int) today()->diffInDays($this->expiry_date->startOfDay(), false);
    }

    public function stockCostValue(): float
    {
        return round((float) $this->quantity * (float) $this->cost_price, 2);
    }

    public function stockSaleValue(): float
    {
        return round((float) $this->quantity * (float) $this->sale_price, 2);
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (blank($term)) {
            return $query;
        }

        return $query->where(function (Builder $query) use ($term) {
            $query->where('name', 'like', "%{$term}%")
                ->orWhere('sku', 'like', "%{$term}%")
                ->orWhere('notes', 'like', "%{$term}%");
        });
    }

    public function scopeLowStock(Builder $query, ?float $threshold = null): Builder
    {
        $threshold ??= Settings::lowStockThreshold();

        return $query
            ->where('quantity', '>', 0)
            ->whereRaw('quantity <= COALESCE(low_stock_threshold, ?)', [$threshold]);
    }

    public function scopeOutOfStock(Builder $query): Builder
    {
        return $query->where('quantity', '<=', 0);
    }

    public function scopeLowOrOutOfStock(Builder $query, ?float $threshold = null): Builder
    {
        $threshold ??= Settings::lowStockThreshold();

        return $query->whereRaw('quantity <= COALESCE(low_stock_threshold, ?)', [$threshold]);
    }

    public function scopeExpiringSoon(Builder $query, ?int $days = null): Builder
    {
        $days ??= Settings::expiryWarningDays();

        return $query
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '>=', today())
            ->whereDate('expiry_date', '<=', today()->addDays($days));
    }

    public function scopeExpired(Builder $query): Builder
    {
        return $query
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<', today());
    }

    public function scopeFilterStatus(Builder $query, ?string $status): Builder
    {
        return match ($status) {
            'low' => $query->lowStock(),
            'out' => $query->outOfStock(),
            'expiring' => $query->expiringSoon(),
            'expired' => $query->expired(),
            'available' => $query->where('quantity', '>', 0)
                ->whereRaw('quantity > COALESCE(low_stock_threshold, ?)', [Settings::lowStockThreshold()]),
            default => $query,
        };
    }
}
