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
        MovementReason $reason,
        User $user,
        ?string $note = null,
        ?Carbon $occurredAt = null,
    ): StockMovement {
        if ($quantity <= 0) {
            throw ValidationException::withMessages([
                'quantity' => 'الكمية يجب أن تكون أكبر من صفر.',
            ]);
        }

        $movement = DB::transaction(function () use ($product, $type, $quantity, $reason, $user, $note, $occurredAt) {
            $locked = Product::query()->whereKey($product->id)->lockForUpdate()->firstOrFail();
            $before = (float) $locked->quantity;
            $after = $type === StockMovementType::In ? $before + $quantity : $before - $quantity;

            if ($after < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'الكمية المطلوبة أكبر من الكمية المتوفرة في المخزون ('.$before.').',
                ]);
            }

            $locked->forceFill(['quantity' => round($after, 2)])->save();

            $movement = $locked->movements()->create([
                'user_id' => $user->id,
                'type' => $type,
                'reason' => $reason,
                'quantity' => $quantity,
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
}
