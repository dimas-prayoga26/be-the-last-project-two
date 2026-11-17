<?php

namespace Tests\Feature\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\Pages\EditProject;
use App\Models\Designer;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class EditProjectTest extends TestCase
{
    use RefreshDatabase;

    public function test_saves_both_translations_of_the_title(): void
    {
        $project = $this->projectWithCover(
            Project::factory()->hasAttached(Designer::factory())->create(['title' => ['en' => 'Old', 'id' => 'Lama']]),
        );
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(EditProject::class, ['record' => $project->getRouteKey()])
            ->fillForm(['title' => ['en' => 'Courtyard House', 'id' => 'Rumah Berhalaman']])
            ->call('save')
            ->assertHasNoFormErrors();

        $project->refresh();
        $this->assertSame('Courtyard House', $project->getTranslation('title', 'en'));
        $this->assertSame('Rumah Berhalaman', $project->getTranslation('title', 'id'));
    }

    public function test_designer_stays_credited_after_removing_themselves(): void
    {
        $designer = Designer::factory()->create();
        $collaborator = Designer::factory()->create();
        $project = $this->projectWithCover(Project::factory()->hasAttached($designer)->create());
        $this->actingAs(User::factory()->designer($designer)->create());

        Livewire::test(EditProject::class, ['record' => $project->getRouteKey()])
            ->fillForm(['designers' => [$collaborator->id]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEqualsCanonicalizing(
            [$designer->id, $collaborator->id],
            $project->designers()->pluck('designers.id')->all(),
        );
    }

    public function test_designer_gets_404_on_a_work_they_are_not_credited_on(): void
    {
        $project = Project::factory()->hasAttached(Designer::factory())->create();
        $this->actingAs(User::factory()->designer()->create());

        $response = $this->get(route('filament.admin.resources.projects.edit', $project));

        $response->assertNotFound();
    }

    /**
     * The edit form requires a cover image.
     */
    private function projectWithCover(Project $project): Project
    {
        Storage::fake('public');
        $project->addMedia(UploadedFile::fake()->image('cover.jpg', 800, 1000))->toMediaCollection('cover');

        return $project;
    }
}
