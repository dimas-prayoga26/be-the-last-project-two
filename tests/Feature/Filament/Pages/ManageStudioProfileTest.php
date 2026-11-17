<?php

namespace Tests\Feature\Filament\Pages;

use App\Filament\Pages\ManageStudioProfile;
use App\Models\StudioProfile;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ManageStudioProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_saves_the_studio_contact_and_about_text(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(ManageStudioProfile::class)
            ->fillForm([
                'about_heading' => ['en' => 'A space for designers', 'id' => 'Ruang bagi desainer'],
                'email' => 'studio@example.com',
                'socials' => [['platform' => 'instagram', 'url' => 'https://instagram.com/example']],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $studio = StudioProfile::current();
        $this->assertSame('Ruang bagi desainer', $studio->getTranslation('about_heading', 'id'));
        $this->assertSame('studio@example.com', $studio->email);
        $this->assertCount(1, $studio->socials);
        $this->assertSame('instagram', $studio->socials[0]['platform']);
        $this->assertSame('https://instagram.com/example', $studio->socials[0]['url']);
    }

    public function test_designer_gets_403(): void
    {
        $this->actingAs(User::factory()->designer()->create());

        $response = $this->get(ManageStudioProfile::getUrl());

        $response->assertForbidden();
    }
}
