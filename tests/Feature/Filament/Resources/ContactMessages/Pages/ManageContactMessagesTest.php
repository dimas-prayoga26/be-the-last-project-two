<?php

namespace Tests\Feature\Filament\Resources\ContactMessages\Pages;

use App\Filament\Resources\ContactMessages\Pages\ManageContactMessages;
use App\Models\ContactMessage;
use App\Models\User;
use Filament\Actions\Testing\TestAction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManageContactMessagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_marks_a_message_as_read(): void
    {
        $message = ContactMessage::factory()->create();
        $this->actingAs(User::factory()->admin()->create());
        $this->freezeTime();

        Livewire::test(ManageContactMessages::class)
            ->callAction(TestAction::make('markAsRead')->table($message));

        $this->assertSame(now()->toDateTimeString(), $message->fresh()->read_at?->toDateTimeString());
    }

    public function test_designer_gets_403(): void
    {
        $this->actingAs(User::factory()->designer()->create());

        $response = $this->get(route('filament.admin.resources.contact-messages.index'));

        $response->assertForbidden();
    }
}
