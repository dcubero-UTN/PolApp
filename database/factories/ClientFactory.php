<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => $this->faker->name(),
            'phone_primary' => $this->faker->numerify('########'),
            'address_details' => $this->faker->address(),
            'collection_day' => 'Lunes',
            'collection_frequency' => 'Semanal',
            'current_balance' => 0,
        ];
    }
}
