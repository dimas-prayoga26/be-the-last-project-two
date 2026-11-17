<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Enums\Discipline;
use App\Models\Category;
use App\Models\Designer;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_only_published_projects_newest_first(): void
    {
        $older = Project::factory()->create(['published_at' => now()->subDays(10)]);
        $newer = Project::factory()->create(['published_at' => now()->subDay()]);
        Project::factory()->draft()->create();
        Project::factory()->create(['published_at' => now()->addDay()]);

        $response = $this->getJson(route('api.v1.projects.index'));

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.slug', $newer->slug)
            ->assertJsonPath('data.1.slug', $older->slug)
            ->assertJsonPath('meta.total', 2);
    }

    public function test_filters_projects_by_discipline_and_category(): void
    {
        $interiorResidential = Category::factory()->inDiscipline(Discipline::Interior)->create(['slug' => 'residential']);
        $architectureResidential = Category::factory()->inDiscipline(Discipline::Architecture)->create(['slug' => 'residential']);
        $interiorCommercial = Category::factory()->inDiscipline(Discipline::Interior)->create(['slug' => 'commercial']);
        $match = Project::factory()->for($interiorResidential)->create();
        Project::factory()->for($architectureResidential)->create();
        Project::factory()->for($interiorCommercial)->create();

        $response = $this->getJson(route('api.v1.projects.index', ['discipline' => 'interior', 'category' => 'residential']));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', $match->slug)
            ->assertJsonPath('data.0.discipline', 'interior')
            ->assertJsonPath('data.0.category.slug', 'residential');
    }

    public function test_filters_projects_by_published_designer(): void
    {
        $designer = Designer::factory()->create();
        $hiddenDesigner = Designer::factory()->unpublished()->create();
        $byDesigner = Project::factory()->hasAttached($designer)->create();
        Project::factory()->hasAttached($hiddenDesigner)->create();
        Project::factory()->create();

        $visible = $this->getJson(route('api.v1.projects.index', ['designer' => $designer->slug]));
        $hidden = $this->getJson(route('api.v1.projects.index', ['designer' => $hiddenDesigner->slug]));

        $visible->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', $byDesigner->slug)
            ->assertJsonPath('data.0.designers.0.slug', $designer->slug);
        $hidden->assertOk()->assertJsonCount(0, 'data');
    }

    public function test_filters_featured_projects(): void
    {
        $featured = Project::factory()->featured()->create();
        Project::factory()->create();

        $response = $this->getJson(route('api.v1.projects.index', ['featured' => 1]));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.slug', $featured->slug);
    }

    public function test_returns_422_for_an_unknown_discipline(): void
    {
        $response = $this->getJson(route('api.v1.projects.index', ['discipline' => 'gardening']));

        $response->assertUnprocessable()->assertJsonValidationErrors(['discipline']);
    }

    public function test_shows_a_project_in_the_requested_language(): void
    {
        $project = Project::factory()->create([
            'title' => ['en' => 'Courtyard House', 'id' => 'Rumah Berhalaman'],
        ]);

        $response = $this->getJson(route('api.v1.projects.show', $project), ['Accept-Language' => 'id-ID,id;q=0.9']);

        $response->assertOk()
            ->assertHeader('Content-Language', 'id')
            ->assertJsonPath('data.title', 'Rumah Berhalaman');
    }

    public function test_falls_back_to_english_when_a_translation_is_missing(): void
    {
        $project = Project::factory()->create(['title' => ['en' => 'Slatted House']]);

        $response = $this->getJson(route('api.v1.projects.show', $project), ['Accept-Language' => 'id']);

        $response->assertOk()->assertJsonPath('data.title', 'Slatted House');
    }

    public function test_returns_404_for_a_draft_project(): void
    {
        $project = Project::factory()->draft()->create();

        $response = $this->getJson(route('api.v1.projects.show', $project));

        $response->assertNotFound();
    }

    public function test_shows_neighbours_in_the_same_discipline_and_related_works_by_the_same_designer(): void
    {
        $designer = Designer::factory()->create();
        $category = Category::factory()->inDiscipline(Discipline::Architecture)->create();
        $otherDiscipline = Category::factory()->inDiscipline(Discipline::Design)->create();
        $newest = Project::factory()->for($category)->create(['published_at' => now()->subDay()]);
        $current = Project::factory()->for($category)->hasAttached($designer)->create(['published_at' => now()->subDays(2)]);
        $oldest = Project::factory()->for($category)->create(['published_at' => now()->subDays(3)]);
        $related = Project::factory()->for($otherDiscipline)->hasAttached($designer)->create();

        $response = $this->getJson(route('api.v1.projects.show', $current));

        $response->assertOk()
            ->assertJsonPath('data.designers.0.slug', $designer->slug)
            ->assertJsonPath('previous.slug', $newest->slug)
            ->assertJsonPath('next.slug', $oldest->slug)
            ->assertJsonCount(1, 'related')
            ->assertJsonPath('related.0.slug', $related->slug);
    }
}
