<?php

namespace Database\Factories;

use App\Enums\SocialPlatform;
use App\Models\Designer;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Designer>
 */
class DesignerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->name(),
            'title' => ['en' => 'Interior Designer', 'id' => 'Desainer Interior'],
            'email' => fake()->unique()->safeEmail(),
            'location' => fake()->city(),
            'headline' => ['en' => fake()->sentence(5), 'id' => fake()->sentence(5)],
            'bio' => ['en' => fake()->paragraph(), 'id' => fake()->paragraph()],
            'statement' => ['en' => fake()->sentence(14), 'id' => fake()->sentence(14)],
            'socials' => [
                ['platform' => SocialPlatform::Instagram->value, 'url' => 'https://instagram.com/'.fake()->userName()],
            ],
            'is_published' => true,
            'sort_order' => 0,
        ];
    }

    /**
     * Indicate that the designer is hidden from the public site.
     */
    public function unpublished(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_published' => false,
        ]);
    }
}
