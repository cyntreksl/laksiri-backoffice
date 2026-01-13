<?php

namespace Database\Factories;

use App\Enum\TokenStatus;
use App\Models\Token;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class TokenFactory extends Factory
{
    protected $model = Token::class;

    public function definition(): array
    {
        return [
            'hbl_id' => null,
            'customer_id' => User::factory(),
            'receptionist_id' => User::factory(),
            'reference' => 'REF-' . $this->faker->unique()->numberBetween(1000, 9999),
            'package_count' => $this->faker->numberBetween(1, 10),
            'token' => 'T-' . $this->faker->unique()->numberBetween(1000, 9999),
            'status' => TokenStatus::ONGOING,
            'departed_by' => null,
            'departed_at' => null,
            'is_cancelled' => false,
            'cancelled_at' => null,
            'cancelled_by' => null,
            'cancellation_reason' => null,
        ];
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TokenStatus::COMPLETED,
            'departed_at' => now(),
            'departed_by' => User::factory(),
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TokenStatus::CANCELLED,
            'is_cancelled' => true,
            'cancelled_at' => now(),
            'cancelled_by' => User::factory(),
            'cancellation_reason' => $this->faker->sentence(),
        ]);
    }

    public function due(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => TokenStatus::DUE,
            'created_at' => now()->subDay(),
        ]);
    }
}
