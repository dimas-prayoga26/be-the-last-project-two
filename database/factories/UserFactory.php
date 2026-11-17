<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\Designer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the user manages the whole admin panel.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Admin,
            'designer_id' => null,
        ]);
    }

    /**
     * Indicate that the user is a designer account for the given (or a new) designer profile.
     */
    public function designer(?Designer $designer = null): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => UserRole::Designer,
            'designer_id' => $designer?->id ?? Designer::factory(),
        ]);
    }
}
