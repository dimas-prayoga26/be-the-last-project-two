<?php

namespace Tests\Feature\Policies;

use App\Models\Category;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CategoryPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_cannot_delete_a_category_that_still_holds_works(): void
    {
        $admin = User::factory()->admin()->create();
        $used = Category::factory()->create();
        Project::factory()->for($used)->create();
        $empty = Category::factory()->create();

        $this->assertFalse($admin->can('delete', $used));
        $this->assertTrue($admin->can('delete', $empty));
    }

    public function test_designer_cannot_manage_categories(): void
    {
        $user = User::factory()->designer()->create();

        $this->assertFalse($user->can('viewAny', Category::class));
        $this->assertFalse($user->can('create', Category::class));
    }
}
