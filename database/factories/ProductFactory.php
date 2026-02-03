<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'sku' => $this->faker->unique()->ean13(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->sentence(),
            'cost_price' => $this->faker->randomFloat(2, 1000, 50000),
            'sale_price' => $this->faker->randomFloat(2, 60000, 100000),
            'stock' => $this->faker->numberBetween(0, 100),
            'min_stock_alert' => 5,
        ];
    }
}
