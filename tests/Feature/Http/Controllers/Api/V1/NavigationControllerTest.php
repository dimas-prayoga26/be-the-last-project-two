<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Enums\Discipline;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NavigationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_groups_categories_under_the_three_disciplines_in_display_order(): void
    {
        Category::factory()->inDiscipline(Discipline::Interior)->create(['name' => ['en' => 'Residential'], 'sort_order' => 2]);
        Category::factory()->inDiscipline(Discipline::Interior)->create(['name' => ['en' => 'Commercial'], 'sort_order' => 1]);
        Category::factory()->inDiscipline(Discipline::Design)->create(['name' => ['en' => 'Furniture']]);

        $response = $this->getJson(route('api.v1.navigation'));

        $response->assertOk()
            ->assertJsonPath('data.0.key', 'architecture')
            ->assertJsonCount(0, 'data.0.categories')
            ->assertJsonPath('data.1.key', 'interior')
            ->assertJsonPath('data.1.categories.0.name', 'Commercial')
            ->assertJsonPath('data.1.categories.1.name', 'Residential')
            ->assertJsonPath('data.2.key', 'design')
            ->assertJsonPath('data.2.categories.0.slug', 'furniture');
    }
}
