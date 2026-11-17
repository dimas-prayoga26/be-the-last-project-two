<?php

namespace Tests\Feature\Filament\Resources\MemberApplications\Pages;

use App\Enums\ApplicationStatus;
use App\Filament\Resources\MemberApplications\Pages\ManageMemberApplications;
use App\Models\MemberApplication;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManageMemberApplicationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_accepts_a_pending_application(): void
    {
        $application = MemberApplication::factory()->create();
        $this->actingAs(User::factory()->admin()->create());
        $this->freezeTime();

        Livewire::test(ManageMemberApplications::class)
            ->callAction(TestAction::make('accept')->table($application));

        $application->refresh();
        $this->assertSame(ApplicationStatus::Accepted, $application->status);
        $this->assertSame(now()->toDateTimeString(), $application->reviewed_at?->toDateTimeString());
    }

    public function test_admin_declines_an_application(): void
    {
        $application = MemberApplication::factory()->create();
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(ManageMemberApplications::class)
            ->callAction(TestAction::make('decline')->table($application));

        $this->assertSame(ApplicationStatus::Declined, $application->fresh()->status);
    }

    public function test_filters_applications_by_status(): void
    {
        $pending = MemberApplication::factory()->create();
        $accepted = MemberApplication::factory()->accepted()->create();
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(ManageMemberApplications::class)
            ->filterTable('status', ApplicationStatus::Pending->value)
            ->assertCanSeeTableRecords([$pending])
            ->assertCanNotSeeTableRecords([$accepted]);
    }

    public function test_designer_gets_403(): void
    {
        $this->actingAs(User::factory()->designer()->create());

        $response = $this->get(route('filament.admin.resources.member-applications.index'));

        $response->assertForbidden();
    }
}
