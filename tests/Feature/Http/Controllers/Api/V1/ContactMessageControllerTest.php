<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Models\ContactMessage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContactMessageControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_message_is_stored_and_returns_201(): void
    {
        $response = $this->postJson(route('api.v1.contact-messages.store'), [
            'name' => 'Rani',
            'email' => 'rani@example.com',
            'phone' => '+62 812 1111 2222',
            'subject' => 'New project',
            'message' => 'We would like to discuss a house in Bandung.',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('contact_messages', [
            'id' => $response->json('data.id'),
            'email' => 'rani@example.com',
            'subject' => 'New project',
            'read_at' => null,
        ]);
    }

    public function test_empty_message_returns_422_with_the_required_fields(): void
    {
        $response = $this->postJson(route('api.v1.contact-messages.store'), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors([
                'name' => 'The name field is required.',
                'email' => 'The email field is required.',
                'message' => 'The message field is required.',
            ]);
        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_filled_honeypot_returns_422_and_stores_nothing(): void
    {
        $response = $this->postJson(route('api.v1.contact-messages.store'), [
            'name' => 'Bot',
            'email' => 'bot@example.com',
            'message' => 'Buy now',
            'website' => 'https://spam.example',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['website']);
        $this->assertDatabaseCount('contact_messages', 0);
    }

    public function test_returns_429_after_five_messages_in_a_minute(): void
    {
        $message = ContactMessage::factory()->make()->only(['name', 'email', 'message']);

        foreach (range(1, 5) as $attempt) {
            $this->postJson(route('api.v1.contact-messages.store'), $message)->assertCreated();
        }
        $response = $this->postJson(route('api.v1.contact-messages.store'), $message);

        $response->assertTooManyRequests();
        $this->assertDatabaseCount('contact_messages', 5);
    }
}
