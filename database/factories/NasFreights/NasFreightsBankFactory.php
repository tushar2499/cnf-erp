<?php

namespace Database\Factories\NasFreights;

use App\Models\NasFreights\NasFreightsBank;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<NasFreightsBank>
 */
class NasFreightsBankFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_name'   => fake()->company(),
            'account_number' => fake()->unique()->numerify('##########'),
            'account_type'   => fake()->randomElement(['Current', 'Savings', 'SND']),
            'bank_name'      => fake()->company().' Bank',
            'bank_branch'    => fake()->city(),
            'routing_number' => fake()->numerify('#########'),
            'is_active'      => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
