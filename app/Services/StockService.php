<?php

namespace App\Services;

use App\Enums\MovementReason;
use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    public function record(
        Product $product,
        StockMovementType $type,
        float $quantity,
        ?MovementReason $reason,
        User $user,
        ?string $note = null,
        ?Carbon $occurredAt = null,
        ?Carbon $expiryDate = null,
    ): StockMovement {
        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'الكمية يجب أن تكون أكبر من صفر.',
            ]);
        }

        if ($type === StockMovementType::Out && $reason === null) {
            throw ValidationException::withMessages([
                'reason' => 'سبب الحركة مطلوب عند الإخراج.',
            ]);
        }

        $movement = DB::transaction(function () use ($product, $type, $quantity, $reason, $user, $note, $occurredAt, $expiryDate) {
            $locked = Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();
            $before = (float) $locked->quantity;
            $after = $type === StockMovementType::In ? $before + $quantity : $before - $quantity;

            if ($after < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'الكمية المطلوبة أكبر من الكمية المتوفرة في المخزون ('.$before.').',
                ]);
            }

            if ($type === StockMovementType::Out) {
                $this->consumeBatches($locked, $quantity);
            }

            $locked->forceFill(['quantity' => round($after, 2)])->save();

            $movement = $locked->movements()->create([
                'user_id' => $user->id,
                'type' => $type,
                'reason' => $type === StockMovementType::Out ? $reason : null,
                'expiry_date' => $type === StockMovementType::In ? $expiryDate : null,
                'quantity' => $quantity,
                'quantity_remaining' => $type === StockMovementType::In ? $quantity : null,
                'quantity_before' => $before,
                'quantity_after' => round($after, 2),
                'note' => $note,
                'occurred_at' => $occurredAt ?? now(),
            ]);

            $product->setRawAttributes($locked->getAttributes(), true);

            return $movement;
        });

        app(AlertService::class)->evaluateProduct($product->refresh());

        return $movement;
    }

    protected function consumeBatches(Product $product, float $quantity): void
    {
        $batches = StockMovement::query()
            ->activeBatches()
            ->where('product_id', $product->id)
            ->fefo()
            ->lockForUpdate()
            ->get();

        $available = round((float) $batches->sum(fn (StockMovement $batch) => (float) $batch->quantity_remaining), 2);

        if ($available < $quantity) {
            throw ValidationException::withMessages([
                'quantity' => 'تعذر مطابقة الكمية المطلوبة مع دفعات المخزون المتاحة ('.$available.').',
            ]);
        }

        $remaining = $quantity;

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $availableInBatch = (float) $batch->quantity_remaining;
            $deducted = min($availableInBatch, $remaining);

            $batch->forceFill([
                'quantity_remaining' => round($availableInBatch - $deducted, 2),
            ])->save();

            $remaining = round($remaining - $deducted, 2);
        }
    }
}
