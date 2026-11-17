<?php

namespace Database\Factories;

use App\Enums\ApplicationStatus;
use App\Enums\Discipline;
use App\Models\MemberApplication;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MemberApplication>
 */
class MemberApplicationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'city' => fake()->city(),
            'discipline' => fake()->randomElement(Discipline::cases()),
            'portfolio_url' => fake()->url(),
            'instagram' => '@'.fake()->userName(),
            'message' => fake()->paragraph(),
        ];
    }

    /**
     * Indicate that an admin accepted the application.
     */
    public function accepted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ApplicationStatus::Accepted,
            'reviewed_at' => now(),
        ]);
    }

    /**
     * Indicate that an admin declined the application.
     */
    public function declined(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ApplicationStatus::Declined,
            'reviewed_at' => now(),
        ]);
    }
}
