<?php

namespace App\Services;

use App\Enums\AlertType;
use App\Enums\ExpiryStatus;
use App\Enums\StockStatus;
use App\Events\StockAlertCreated;
use App\Models\Product;
use App\Models\User;
use App\Notifications\StockAlertNotification;
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
        $product->refresh()->load('activeBatches');

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
        $expiryDate = $product->nearestExpiryDate();

        if ($expiryStatus === ExpiryStatus::Expired && $expiryDate) {
            $sent += $this->notify(
                $product,
                AlertType::Expiry,
                'انتهت صلاحية دفعة',
                "انتهت صلاحية أقرب دفعة من «{$product->name}» بتاريخ {$expiryDate->format('Y/m/d')}.",
                'danger',
            );
        } elseif ($expiryStatus === ExpiryStatus::Expiring && $expiryDate) {
            $days = $product->daysUntilExpiry();
            $sent += $this->notify(
                $product,
                AlertType::Expiry,
                'دفعة قريبة الانتهاء',
                "تنتهي أقرب دفعة من «{$product->name}» بعد {$days} يوم بتاريخ {$expiryDate->format('Y/m/d')}.",
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

        foreach ($this->recipients() as $user) {
            if ($this->alreadyUnread($user, $product, $type)) {
                continue;
            }

            $user->notify(new StockAlertNotification($product, $type, $title, $message, $tone));
            $sent++;

            $notification = $user->notifications()->latest()->first();

            if ($notification) {
                try {
                    StockAlertCreated::dispatch($user, $notification);
                } catch (\Throwable $exception) {
                    report($exception);
                }
            }
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

    protected function recipients(): Collection
    {
        return User::query()->get();
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
