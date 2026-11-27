<?php

namespace Tests\Feature\Providers\Filament;

use App\Models\StudioProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPanelProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_shows_the_studio_about_image(): void
    {
        Storage::fake('public');
        $studio = StudioProfile::current();
        $studio->addMedia(UploadedFile::fake()->image('studio.jpg', 1200, 800))->toMediaCollection('about_image');

        $response = $this->get(route('filament.admin.auth.login'));

        $response->assertOk();
        $response->assertSee('tlp-auth-visual', false);
        $response->assertSee($studio->fresh()->getFirstMediaUrl('about_image', 'large'), false);
    }

    public function test_login_page_renders_without_creating_a_studio_profile(): void
    {
        $response = $this->get(route('filament.admin.auth.login'));

        $response->assertOk();
        $response->assertSee('tlp-auth-visual', false);
        $this->assertDatabaseCount('studio_profiles', 0);
    }
}
