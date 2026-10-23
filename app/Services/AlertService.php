<?php

namespace App\Services;

use App\Enums\AlertType;
use App\Enums\ExpiryStatus;
use App\Enums\StockStatus;
use App\Models\Product;
use App\Models\User;
use App\Notifications\StockAlertNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AlertService
{
    public function checkAll(): int
    {
        $sent = 0;

        Product::query()->with('category')->chunkById(100, function (Collection $products) use (&$sent) {
            foreach ($products as $product) {
                $sent += $this->evaluateProduct($product);
            }
        });

        return $sent;
    }

    public function evaluateProduct(Product $product): int
    {
        $sent = 0;
        $product->refresh();

        $stockStatus = $product->stockStatus();

        if ($stockStatus === StockStatus::Out) {
            $sent += $this->notify(
                $product,
                AlertType::LowStock,
                'نفد المخزون',
                "نفدت كمية «{$product->name}» بالكامل من المستودع.",
                'danger',
            );
        } elseif ($stockStatus === StockStatus::Low) {
            $sent += $this->notify(
                $product,
                AlertType::LowStock,
                'مخزون منخفض',
                "الكمية المتبقية من «{$product->name}» هي {$product->quantity} {$product->unit} فقط.",
                'warning',
            );
        } else {
            $this->resolve($product, AlertType::LowStock);
        }

        $expiryStatus = $product->expiryStatus();

        if ($expiryStatus === ExpiryStatus::Expired) {
            $sent += $this->notify(
                $product,
                AlertType::Expiry,
                'انتهت صلاحية منتج',
                "انتهت صلاحية «{$product->name}» بتاريخ {$product->expiry_date->format('Y/m/d')}.",
                'danger',
            );
        } elseif ($expiryStatus === ExpiryStatus::Expiring) {
            $days = $product->daysUntilExpiry();
            $sent += $this->notify(
                $product,
                AlertType::Expiry,
                'منتج قريب الانتهاء',
                "ينتهي «{$product->name}» بعد {$days} يوم بتاريخ {$product->expiry_date->format('Y/m/d')}.",
                'warning',
            );
        } else {
            $this->resolve($product, AlertType::Expiry);
        }

        return $sent;
    }

    protected function notify(Product $product, AlertType $type, string $title, string $message, string $tone): int
    {
        $sent = 0;

        foreach ($this->recipients($type) as $user) {
            if ($this->alreadyUnread($user, $product, $type)) {
                continue;
            }

            $user->notify(new StockAlertNotification($product, $type, $title, $message, $tone));
            $sent++;
        }

        return $sent;
    }

    public function resolveProduct(Product $product): void
    {
        $this->resolve($product, AlertType::LowStock);
        $this->resolve($product, AlertType::Expiry);
    }

    protected function resolve(Product $product, AlertType $type): void
    {
        DB::table('notifications')
            ->where('type', StockAlertNotification::class)
            ->whereNull('read_at')
            ->where('data->product_id', $product->id)
            ->where('data->alert_type', $type->value)
            ->update(['read_at' => now()]);
    }

    protected function recipients(AlertType $type): Collection
    {
        return User::query()
            ->active()
            ->where(function (Builder $query) use ($type) {
                $query->where('role', 'admin')
                    ->orWhere($type->preferenceColumn(), true);
            })
            ->get();
    }

    protected function alreadyUnread(User $user, Product $product, AlertType $type): bool
    {
        return $user->notifications()
            ->where('type', StockAlertNotification::class)
            ->whereNull('read_at')
            ->where('data->product_id', $product->id)
            ->where('data->alert_type', $type->value)
            ->exists();
    }
}
