<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Product;
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
            'expiry_date' => fake()->optional(0.75)->dateTimeBetween('-20 days', '+300 days'),
            'cost_price' => $cost,
            'sale_price' => round($cost * fake()->randomFloat(2, 1.1, 1.7), 2),
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }

    public function outOfStock(): static
    {
        return $this->state(fn () => ['quantity' => 0]);
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
        return $this->state(fn () => [
            'expiry_date' => now()->addDays(fake()->numberBetween(1, 25)),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn () => [
            'expiry_date' => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }
}
