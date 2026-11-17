<?php

namespace Tests\Feature\Policies;

use App\Models\Designer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DesignerPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_designers(): void
    {
        $admin = User::factory()->admin()->create();
        $designer = Designer::factory()->create();

        $this->assertTrue($admin->can('create', Designer::class));
        $this->assertTrue($admin->can('update', $designer));
        $this->assertTrue($admin->can('delete', $designer));
    }

    public function test_designer_can_update_only_their_own_profile(): void
    {
        $own = Designer::factory()->create();
        $other = Designer::factory()->create();
        $user = User::factory()->designer($own)->create();

        $this->assertTrue($user->can('update', $own));
        $this->assertFalse($user->can('update', $other));
    }

    public function test_designer_cannot_create_or_delete_designers(): void
    {
        $own = Designer::factory()->create();
        $user = User::factory()->designer($own)->create();

        $this->assertFalse($user->can('create', Designer::class));
        $this->assertFalse($user->can('delete', $own));
    }
}
