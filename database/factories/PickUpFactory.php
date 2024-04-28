<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PickUp>
 */
class PickUpFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reference' => $this->faker->unique()->word,
            'agent_id' => $this->faker->numberBetween(1, 10),
            'cargo_type' => $this->faker->randomElement(['sea', 'air']),
            'name' => $this->faker->name,
            'email' => $this->faker->unique()->safeEmail,
            'contact_number' => $this->faker->phoneNumber,
            'address' => $this->faker->address,
            'location_name' => $this->faker->city,
            'location_longitude' => $this->faker->longitude,
            'location_latitude' => $this->faker->latitude,
            'zone_id' => $this->faker->numberBetween(1, 5),
            'notes' => $this->faker->sentence,
            'driver_id' => null,
            'driver_assigned_at' => null,
            'hbl_id' => null,
            'created_by' => 1,
        ];
    }
}
