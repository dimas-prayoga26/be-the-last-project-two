<?php

namespace Tests\Feature\Policies;

use App\Models\MemberApplication;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberApplicationPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_reviews_applications_but_never_creates_them(): void
    {
        $admin = User::factory()->admin()->create();
        $application = MemberApplication::factory()->create();

        $this->assertTrue($admin->can('viewAny', MemberApplication::class));
        $this->assertTrue($admin->can('update', $application));
        $this->assertTrue($admin->can('delete', $application));
        $this->assertFalse($admin->can('create', MemberApplication::class));
    }

    public function test_designer_cannot_see_applications(): void
    {
        $user = User::factory()->designer()->create();
        $application = MemberApplication::factory()->create();

        $this->assertFalse($user->can('viewAny', MemberApplication::class));
        $this->assertFalse($user->can('view', $application));
        $this->assertFalse($user->can('update', $application));
    }
}
