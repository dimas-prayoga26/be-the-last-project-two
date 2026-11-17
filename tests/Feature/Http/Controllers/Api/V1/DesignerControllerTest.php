<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Enums\Discipline;
use App\Models\Category;
use App\Models\Designer;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DesignerControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_published_designers_in_display_order_with_published_project_counts(): void
    {
        $second = Designer::factory()->create(['sort_order' => 2]);
        $first = Designer::factory()->create(['sort_order' => 1]);
        Designer::factory()->unpublished()->create();
        Project::factory()->count(2)->hasAttached($first)->create();
        Project::factory()->draft()->hasAttached($first)->create();

        $response = $this->getJson(route('api.v1.designers.index'));

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.slug', $first->slug)
            ->assertJsonPath('data.0.projects_count', 2)
            ->assertJsonPath('data.1.slug', $second->slug)
            ->assertJsonPath('data.1.projects_count', 0);
    }

    public function test_shows_a_designer_with_only_published_projects(): void
    {
        $designer = Designer::factory()->create(['email' => 'sekar@example.com']);
        $published = Project::factory()->hasAttached($designer)->create();
        Project::factory()->draft()->hasAttached($designer)->create();

        $response = $this->getJson(route('api.v1.designers.show', $designer));

        $response->assertOk()
            ->assertJsonPath('data.email', 'sekar@example.com')
            ->assertJsonCount(1, 'data.projects')
            ->assertJsonPath('data.projects.0.slug', $published->slug);
    }

    public function test_shows_the_profile_texts_and_the_disciplines_of_published_works(): void
    {
        $designer = Designer::factory()->create([
            'headline' => ['en' => 'Quiet houses', 'id' => 'Rumah yang tenang'],
            'statement' => ['en' => 'Shade first.', 'id' => 'Bayangan dulu.'],
        ]);
        Project::factory()->for(Category::factory()->inDiscipline(Discipline::Design))->hasAttached($designer)->create();
        Project::factory()->for(Category::factory()->inDiscipline(Discipline::Architecture))->hasAttached($designer)->create();
        Project::factory()->draft()->for(Category::factory()->inDiscipline(Discipline::Interior))->hasAttached($designer)->create();

        $response = $this->getJson(route('api.v1.designers.show', $designer), ['Accept-Language' => 'id']);

        $response->assertOk()
            ->assertJsonPath('data.headline', 'Rumah yang tenang')
            ->assertJsonPath('data.statement', 'Bayangan dulu.')
            ->assertJsonPath('data.disciplines', ['architecture', 'design']);
    }

    public function test_returns_404_for_an_unpublished_designer(): void
    {
        $designer = Designer::factory()->unpublished()->create();

        $response = $this->getJson(route('api.v1.designers.show', $designer));

        $response->assertNotFound();
    }
}
