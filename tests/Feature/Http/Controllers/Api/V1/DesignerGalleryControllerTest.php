<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Enums\Discipline;
use App\Models\Category;
use App\Models\Designer;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Tests\TestCase;

class DesignerGalleryControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_lists_the_images_of_published_works_in_portfolio_order_with_each_cover_first(): void
    {
        $designer = Designer::factory()->create();
        $first = Project::factory()->hasAttached($designer)->create(['sort_order' => 1]);
        $second = Project::factory()->hasAttached($designer)->create(['sort_order' => 2]);
        $draft = Project::factory()->draft()->hasAttached($designer)->create(['sort_order' => 0]);
        $someoneElses = Project::factory()->create(['sort_order' => 0]);

        $firstGallery = $this->addImage($first, 'gallery');
        $firstCover = $this->addImage($first, 'cover');
        $secondCover = $this->addImage($second, 'cover');
        $this->addImage($draft, 'cover');
        $this->addImage($someoneElses, 'cover');

        $response = $this->getJson(route('api.v1.designers.gallery', $designer));

        $response->assertOk()
            ->assertJsonCount(3, 'data')
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('data.0.id', $firstCover->id)
            ->assertJsonPath('data.0.width', 1200)
            ->assertJsonPath('data.0.work.slug', $first->slug)
            ->assertJsonPath('data.0.work.discipline', $first->category->discipline->value)
            ->assertJsonPath('data.1.id', $firstGallery->id)
            ->assertJsonPath('data.2.id', $secondCover->id)
            ->assertJsonPath('data.2.work.slug', $second->slug);
    }

    public function test_filters_the_gallery_by_discipline(): void
    {
        $designer = Designer::factory()->create();
        $interior = Project::factory()
            ->for(Category::factory()->inDiscipline(Discipline::Interior))
            ->hasAttached($designer)
            ->create();
        $architecture = Project::factory()
            ->for(Category::factory()->inDiscipline(Discipline::Architecture))
            ->hasAttached($designer)
            ->create();
        $interiorCover = $this->addImage($interior, 'cover');
        $this->addImage($architecture, 'cover');

        $response = $this->getJson(route('api.v1.designers.gallery', ['designer' => $designer, 'discipline' => 'interior']));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $interiorCover->id);
    }

    public function test_filters_the_gallery_by_work(): void
    {
        $designer = Designer::factory()->create();
        $chosen = Project::factory()->hasAttached($designer)->create();
        $other = Project::factory()->hasAttached($designer)->create();
        $chosenCover = $this->addImage($chosen, 'cover');
        $this->addImage($other, 'cover');

        $response = $this->getJson(route('api.v1.designers.gallery', ['designer' => $designer, 'work' => $chosen->slug]));

        $response->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $chosenCover->id);
    }

    public function test_rejects_an_unknown_discipline(): void
    {
        $designer = Designer::factory()->create();

        $response = $this->getJson(route('api.v1.designers.gallery', ['designer' => $designer, 'discipline' => 'fashion']));

        $response->assertUnprocessable()->assertJsonValidationErrors(['discipline']);
    }

    public function test_returns_404_for_an_unpublished_designer(): void
    {
        $designer = Designer::factory()->unpublished()->create();

        $response = $this->getJson(route('api.v1.designers.gallery', $designer));

        $response->assertNotFound();
    }

    private function addImage(Project $project, string $collection): Media
    {
        return $project->addMedia(UploadedFile::fake()->image("{$collection}.jpg", 1200, 800))->toMediaCollection($collection);
    }
}
