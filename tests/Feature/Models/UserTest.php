<?php

namespace Tests\Feature\Models;

use App\Enums\UserRole;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    public function test_admins_and_linked_designers_can_open_the_admin_panel(): void
    {
        $admin = User::factory()->admin()->create();
        $designer = User::factory()->designer()->create();

        $this->assertTrue($admin->canAccessPanel(Filament::getPanel('admin')));
        $this->assertTrue($designer->canAccessPanel(Filament::getPanel('admin')));
    }

    public function test_designer_account_without_a_profile_cannot_open_the_admin_panel(): void
    {
        $user = User::factory()->create(['role' => UserRole::Designer, 'designer_id' => null]);

        $this->assertFalse($user->canAccessPanel(Filament::getPanel('admin')));
    }
}
