<?php

namespace Tests\Feature\Listeners;

use App\Listeners\CompressProjectImage;
use App\Models\Designer;
use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompressProjectImageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_compresses_a_work_photo_over_one_megabyte_to_below_the_limit(): void
    {
        $photo = $this->noisyImage('hall.jpg', 1400, 1000);
        $this->assertGreaterThan(CompressProjectImage::MAX_BYTES, filesize($photo));

        $media = Project::factory()->create()->addMedia($photo)->toMediaCollection('gallery')->fresh();

        $storedBytes = filesize($media->getPath());
        [$width, $height] = getimagesize($media->getPath());
        $this->assertLessThanOrEqual(CompressProjectImage::MAX_BYTES, $storedBytes);
        $this->assertSame($storedBytes, $media->size);
        $this->assertSame('image/jpeg', $media->mime_type);
        $this->assertSame([$width, $height], [$media->getCustomProperty('width'), $media->getCustomProperty('height')]);
        $this->assertEqualsWithDelta(1.4, $width / $height, 0.01);
        $this->assertTrue($media->hasGeneratedConversion('large'));
    }

    public function test_leaves_a_work_photo_under_the_limit_untouched(): void
    {
        $photo = UploadedFile::fake()->image('facade.jpg', 1600, 1000);
        $originalHash = md5_file($photo->getPathname());

        $media = Project::factory()->create()->addMedia($photo)->toMediaCollection('gallery')->fresh();

        $this->assertSame($originalHash, md5_file($media->getPath()));
        $this->assertSame('facade.jpg', $media->file_name);
    }

    public function test_stores_a_png_work_photo_over_the_limit_as_a_jpeg(): void
    {
        $photo = $this->noisyImage('render.png', 900, 700);
        $this->assertGreaterThan(CompressProjectImage::MAX_BYTES, filesize($photo));

        $media = Project::factory()->create()->addMedia($photo)->toMediaCollection('cover')->fresh();

        $this->assertSame('render.jpg', $media->file_name);
        $this->assertSame('image/jpeg', $media->mime_type);
        $this->assertFileExists($media->getPath());
        $this->assertLessThanOrEqual(CompressProjectImage::MAX_BYTES, filesize($media->getPath()));
        $this->assertFileDoesNotExist(dirname($media->getPath()).'/render.png');
        $this->assertTrue($media->hasGeneratedConversion('card'));
    }

    public function test_leaves_photos_of_other_models_untouched(): void
    {
        $photo = $this->noisyImage('portrait.jpg', 1400, 1000);
        $originalHash = md5_file($photo);

        $media = Designer::factory()->create()->addMedia($photo)->toMediaCollection('photo')->fresh();

        $this->assertSame($originalHash, md5_file($media->getPath()));
    }

    /**
     * Random pixels barely compress, so even a small image is well over 1 MB.
     */
    private function noisyImage(string $fileName, int $width, int $height): string
    {
        mt_srand(2026);
        $image = imagecreatetruecolor($width, $height);

        for ($x = 0; $x < $width; $x++) {
            for ($y = 0; $y < $height; $y++) {
                imagesetpixel($image, $x, $y, mt_rand(0, 0xFFFFFF));
            }
        }

        mt_srand();
        $directory = sys_get_temp_dir().'/'.uniqid('tlp-', true);
        mkdir($directory);
        $path = $directory.'/'.$fileName;
        str_ends_with($fileName, '.png') ? imagepng($image, $path) : imagejpeg($image, $path, 100);

        return $path;
    }
}
