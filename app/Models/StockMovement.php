<?php

namespace App\Models;

use App\Enums\ExpiryStatus;
use App\Enums\MovementReason;
use App\Enums\StockMovementType;
use App\Support\Settings;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_id',
        'user_id',
        'type',
        'reason',
        'expiry_date',
        'quantity',
        'quantity_remaining',
        'quantity_before',
        'quantity_after',
        'note',
        'occurred_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => StockMovementType::class,
            'reason' => MovementReason::class,
            'expiry_date' => 'date',
            'quantity' => 'decimal:2',
            'quantity_remaining' => 'decimal:2',
            'quantity_before' => 'decimal:2',
            'quantity_after' => 'decimal:2',
            'occurred_at' => 'datetime',
        ];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isActiveBatch(): bool
    {
        return $this->type === StockMovementType::In && (float) $this->quantity_remaining > 0;
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

        return (int) today()->diffInDays($this->expiry_date->copy()->startOfDay(), false);
    }

    public function scopeActiveBatches($query)
    {
        return $query
            ->where('type', StockMovementType::In->value)
            ->where('quantity_remaining', '>', 0);
    }

    public function scopeFefo($query)
    {
        return $query
            ->orderByRaw('CASE WHEN expiry_date IS NULL THEN 1 ELSE 0 END')
            ->orderBy('expiry_date')
            ->orderBy('occurred_at')
            ->orderBy('id');
    }

    public function scopeSearch($query, ?string $term)
    {
        if (blank($term)) {
            return $query;
        }

        return $query->whereHas('product', function ($query) use ($term) {
            $query->where('name', 'like', "%{$term}%")
                ->orWhere('sku', 'like', "%{$term}%");
        });
    }
}
