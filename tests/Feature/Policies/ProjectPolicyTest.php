<?php

namespace Tests\Feature\Policies;

use App\Models\Designer;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_every_project(): void
    {
        $admin = User::factory()->admin()->create();
        $project = Project::factory()->create();

        $this->assertTrue($admin->can('update', $project));
        $this->assertTrue($admin->can('delete', $project));
        $this->assertTrue($admin->can('deleteAny', Project::class));
        $this->assertTrue($admin->can('reorder', Project::class));
    }

    public function test_designer_can_manage_projects_they_are_credited_on(): void
    {
        $designer = Designer::factory()->create();
        $user = User::factory()->designer($designer)->create();
        $project = Project::factory()->hasAttached($designer)->create();

        $this->assertTrue($user->can('create', Project::class));
        $this->assertTrue($user->can('update', $project));
        $this->assertTrue($user->can('delete', $project));
    }

    public function test_designer_cannot_touch_projects_of_other_designers(): void
    {
        $user = User::factory()->designer()->create();
        $project = Project::factory()->hasAttached(Designer::factory())->create();

        $this->assertFalse($user->can('view', $project));
        $this->assertFalse($user->can('update', $project));
        $this->assertFalse($user->can('delete', $project));
    }

    public function test_designer_cannot_bulk_delete_or_reorder_projects(): void
    {
        $user = User::factory()->designer()->create();

        $this->assertFalse($user->can('deleteAny', Project::class));
        $this->assertFalse($user->can('reorder', Project::class));
    }
}
