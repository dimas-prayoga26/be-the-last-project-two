<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Models\MemberApplication;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemberApplicationControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_application_is_stored_as_pending_and_returns_201(): void
    {
        $response = $this->postJson(route('api.v1.member-applications.store'), [
            'name' => 'Rani',
            'email' => 'rani@example.com',
            'phone' => '+62 812 1111 2222',
            'city' => 'Bandung',
            'discipline' => 'interior',
            'portfolio_url' => 'https://example.com/rani',
            'instagram' => '@rani.studio',
            'message' => 'I design small cafes and would like to join the collective.',
        ]);

        $response->assertCreated();
        $this->assertDatabaseHas('member_applications', [
            'id' => $response->json('data.id'),
            'email' => 'rani@example.com',
            'discipline' => 'interior',
            'status' => 'pending',
            'reviewed_at' => null,
        ]);
    }

    public function test_empty_application_returns_422_with_the_required_fields(): void
    {
        $response = $this->postJson(route('api.v1.member-applications.store'), []);

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['name', 'email', 'city', 'discipline', 'portfolio_url', 'message']);
        $this->assertDatabaseCount('member_applications', 0);
    }

    public function test_unknown_discipline_and_invalid_portfolio_url_return_422(): void
    {
        $application = MemberApplication::factory()->make()->only(['name', 'email', 'city', 'message']);

        $response = $this->postJson(route('api.v1.member-applications.store'), [
            ...$application,
            'discipline' => 'fashion',
            'portfolio_url' => 'not a link',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['discipline', 'portfolio_url']);
        $this->assertDatabaseCount('member_applications', 0);
    }

    public function test_filled_honeypot_returns_422_and_stores_nothing(): void
    {
        $response = $this->postJson(route('api.v1.member-applications.store'), [
            ...$this->validPayload(),
            'website' => 'https://spam.example',
        ]);

        $response->assertUnprocessable()->assertJsonValidationErrors(['website']);
        $this->assertDatabaseCount('member_applications', 0);
    }

    public function test_returns_429_after_three_applications_in_a_minute(): void
    {
        foreach (range(1, 3) as $attempt) {
            $this->postJson(route('api.v1.member-applications.store'), $this->validPayload())->assertCreated();
        }
        $response = $this->postJson(route('api.v1.member-applications.store'), $this->validPayload());

        $response->assertTooManyRequests();
        $this->assertDatabaseCount('member_applications', 3);
    }

    /**
     * @return array<string, string>
     */
    private function validPayload(): array
    {
        return [
            'name' => 'Bima',
            'email' => 'bima@example.com',
            'city' => 'Jakarta',
            'discipline' => 'architecture',
            'portfolio_url' => 'https://example.com/bima',
            'message' => 'Architect, mostly houses.',
        ];
    }
}
