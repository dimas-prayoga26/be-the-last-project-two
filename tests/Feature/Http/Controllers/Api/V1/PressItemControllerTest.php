<?php

namespace Tests\Feature\Http\Controllers\Api\V1;

use App\Models\PressItem;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PressItemControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_published_press_items_newest_first(): void
    {
        $older = PressItem::factory()->create(['published_on' => '2025-01-10']);
        $newer = PressItem::factory()->create(['published_on' => '2026-03-02']);
        PressItem::factory()->unpublished()->create();

        $response = $this->getJson(route('api.v1.press.index'));

        $response->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('data.0.id', $newer->id)
            ->assertJsonPath('data.0.published_on', '2026-03-02')
            ->assertJsonPath('data.1.id', $older->id);
    }
}
