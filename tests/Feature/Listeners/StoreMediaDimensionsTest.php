<?php

namespace Tests\Feature\Listeners;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class StoreMediaDimensionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_stores_the_pixel_size_of_an_uploaded_image(): void
    {
        Storage::fake('public');
        $project = Project::factory()->create();

        $media = $project->addMedia(UploadedFile::fake()->image('hall.jpg', 1200, 800))->toMediaCollection('gallery');

        $this->assertSame(1200, $media->fresh()->getCustomProperty('width'));
        $this->assertSame(800, $media->fresh()->getCustomProperty('height'));
    }
}
