<?php

namespace Database\Factories;

use App\Enums\StockMovementType;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockMovement;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition(): array
    {
        $cost = fake()->randomFloat(2, 3, 180);

        return [
            'category_id' => Category::factory(),
            'name' => fake()->unique()->words(3, true),
            'sku' => null,
            'unit' => fake()->randomElement(['قطعة', 'علبة', 'كيس', 'كرتون', 'كيلو', 'لتر']),
            'quantity' => fake()->numberBetween(0, 220),
            'low_stock_threshold' => fake()->optional(0.6)->numberBetween(5, 30),
            'cost_price' => $cost,
            'sale_price' => round($cost * fake()->randomFloat(2, 1.1, 1.7), 2),
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }

    public function withBatch(?float $quantity = null, mixed $expiryDate = null): static
    {
        return $this->afterCreating(function (Product $product) use ($quantity, $expiryDate) {
            $batchQuantity = $quantity ?? (float) $product->quantity;

            if ($batchQuantity <= 0) {
                return;
            }

            StockMovement::factory()->create([
                'product_id' => $product->id,
                'user_id' => null,
                'type' => StockMovementType::In,
                'reason' => null,
                'expiry_date' => $expiryDate,
                'quantity' => $batchQuantity,
                'quantity_remaining' => $batchQuantity,
                'quantity_before' => 0,
                'quantity_after' => $batchQuantity,
            ]);
        });
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => ['quantity' => 0]);
    }

    public function depleted(): static
    {
        return $this->afterCreating(function (Product $product) {
            StockMovement::factory()->create([
                'product_id' => $product->id,
                'user_id' => null,
                'type' => StockMovementType::In,
                'reason' => null,
                'expiry_date' => null,
                'quantity' => 10,
                'quantity_remaining' => 0,
                'quantity_before' => 0,
                'quantity_after' => 10,
                'occurred_at' => now()->subDay(),
            ]);

            $product->forceFill(['quantity' => 0])->save();
        });
    }

    public function lowStock(): static
    {
        return $this->state(fn () => [
            'quantity' => fake()->numberBetween(1, 8),
            'low_stock_threshold' => 15,
        ]);
    }

    public function expiringSoon(): static
    {
        return $this->withBatch(expiryDate: now()->addDays(fake()->numberBetween(1, 25)));
    }

    public function expired(): static
    {
        return $this->withBatch(expiryDate: now()->subDays(fake()->numberBetween(1, 30)));
    }
}
