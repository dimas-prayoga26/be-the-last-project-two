<?php

namespace Tests\Feature\Filament\Resources\Projects\Pages;

use App\Filament\Resources\Projects\Pages\ListProjects;
use App\Models\Designer;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ListProjectsTest extends TestCase
{
    use RefreshDatabase;

    public function test_designer_sees_only_the_works_they_are_credited_on(): void
    {
        $designer = Designer::factory()->create();
        $own = Project::factory()->hasAttached($designer)->create();
        $others = Project::factory()->count(2)->hasAttached(Designer::factory())->create();
        $this->actingAs(User::factory()->designer($designer)->create());

        $component = Livewire::test(ListProjects::class);

        $component->assertCanSeeTableRecords([$own])
            ->assertCanNotSeeTableRecords($others);
    }

    public function test_admin_sees_every_work_including_drafts(): void
    {
        $projects = Project::factory()->count(2)->create();
        $draft = Project::factory()->draft()->create();
        $this->actingAs(User::factory()->admin()->create());

        $component = Livewire::test(ListProjects::class);

        $component->assertCanSeeTableRecords([...$projects, $draft]);
    }
}
