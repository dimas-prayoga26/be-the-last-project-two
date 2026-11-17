<?php

namespace Database\Factories;

use App\Models\PressItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PressItem>
 */
class PressItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => ['en' => fake()->sentence(), 'id' => fake()->sentence()],
            'publication' => fake()->company(),
            'published_on' => fake()->dateTimeBetween('-2 years'),
            'url' => fake()->url(),
            'excerpt' => ['en' => fake()->paragraph(), 'id' => fake()->paragraph()],
            'is_published' => true,
        ];
    }

    /**
     * Indicate that the press item is hidden from the public site.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
        ]);
    }
}
