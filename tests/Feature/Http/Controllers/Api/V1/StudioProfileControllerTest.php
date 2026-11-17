<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Models\StudioProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudioProfileControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_the_studio_content_in_the_requested_language(): void
    {
        StudioProfile::current()->update([
            'about_heading' => ['en' => 'A space for designers', 'id' => 'Ruang bagi desainer'],
            'email' => 'hello@example.com',
            'socials' => [['platform' => 'instagram', 'url' => 'https://instagram.com/example']],
        ]);

        $response = $this->getJson(route('api.v1.studio'), ['Accept-Language' => 'id']);

        $response->assertOk()
            ->assertJsonPath('data.about.heading', 'Ruang bagi desainer')
            ->assertJsonPath('data.contact.email', 'hello@example.com')
            ->assertJsonPath('data.socials.0.platform', 'instagram');
    }

    public function test_returns_empty_content_before_the_studio_is_set_up(): void
    {
        $response = $this->getJson(route('api.v1.studio'));

        $response->assertOk()
            ->assertJsonPath('data.about.heading', null)
            ->assertJsonPath('data.socials', []);
    }
}
