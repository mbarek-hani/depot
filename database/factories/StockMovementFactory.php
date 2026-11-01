<?php

namespace Database\Factories;

use App\Enums\MovementReason;
use App\Enums\StockMovementType;
use App\Models\Product;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StockMovement>
 */
class StockMovementFactory extends Factory
{
    protected $model = StockMovement::class;

    public function definition(): array
    {
        $type = fake()->randomElement(StockMovementType::cases());
        $quantity = fake()->randomFloat(2, 1, 25);
        $before = fake()->randomFloat(2, 0, 100);

        return [
            'product_id' => Product::factory(),
            'user_id' => User::factory(),
            'type' => $type,
            'reason' => $type === StockMovementType::Out ? fake()->randomElement(MovementReason::cases()) : null,
            'expiry_date' => $type === StockMovementType::In ? fake()->optional(0.7)->dateTimeBetween('-20 days', '+300 days') : null,
            'quantity' => $quantity,
            'quantity_remaining' => $type === StockMovementType::In ? $quantity : null,
            'quantity_before' => $before,
            'quantity_after' => $type === StockMovementType::In ? $before + $quantity : max(0, $before - $quantity),
            'note' => fake()->optional()->sentence(),
            'occurred_at' => fake()->dateTimeBetween('-6 months', 'now'),
        ];
    }
}
