<?php

namespace Tests\Feature\Filament\Resources\Designers\Pages;

use App\Filament\Resources\Designers\Pages\CreateDesigner;
use App\Models\Designer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CreateDesignerTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejects_a_negative_sort_order_with_a_validation_error(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(CreateDesigner::class)
            ->fillForm(['name' => 'Rani Putri', 'sort_order' => -17])
            ->call('create')
            ->assertHasFormErrors(['sort_order' => 'min']);

        $this->assertDatabaseCount('designers', 0);
    }

    public function test_saves_a_designer_with_a_valid_sort_order(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        Livewire::test(CreateDesigner::class)
            ->fillForm(['name' => 'Rani Putri', 'sort_order' => 5])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(5, Designer::query()->where('name', 'Rani Putri')->value('sort_order'));
    }
}
