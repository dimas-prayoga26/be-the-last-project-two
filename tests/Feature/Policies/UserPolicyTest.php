<?php

namespace Tests\Feature\Policies;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_delete_other_accounts_but_not_their_own(): void
    {
        $admin = User::factory()->admin()->create();
        $other = User::factory()->designer()->create();

        $this->assertTrue($admin->can('delete', $other));
        $this->assertFalse($admin->can('delete', $admin));
    }

    public function test_designer_cannot_manage_accounts(): void
    {
        $user = User::factory()->designer()->create();

        $this->assertFalse($user->can('viewAny', User::class));
    }
}
