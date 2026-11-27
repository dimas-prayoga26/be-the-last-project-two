<?php

namespace App\Listeners;

use App\Models\Project;
use GdImage;
use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

/**
 * Keeps every work photo under 1 MB before its web versions are generated: it lowers the
 * quality step by step and, when that is not enough, the size. PNG photos are stored as
 * JPEG. Photos already under the limit are left untouched.
 */
class CompressProjectImage
{
    public const MAX_BYTES = 1_000_000;

    private const MAX_EDGE = 2000;

    private const MIN_EDGE = 600;

    /**
     * Larger images are skipped: decoding them would exceed the server's PHP memory limit.
     * Uploads from the admin are resized to 2000 px in the browser, so they stay far below.
     */
    private const MAX_PIXELS = 20_000_000;

    private const QUALITIES = [82, 76, 70];

    private const COMPRESSIBLE_TYPES = ['image/jpeg', 'image/png', 'image/webp'];

    public function handle(MediaHasBeenAddedEvent $event): void
    {
        $media = $event->media;
        $path = $media->getPath();

        if ($media->model_type !== (new Project)->getMorphClass()
            || ! in_array($media->mime_type, self::COMPRESSIBLE_TYPES, true)
            || ! is_file($path)
            || filesize($path) <= self::MAX_BYTES) {
            return;
        }

        $dimensions = getimagesize($path);

        if ($dimensions === false || $dimensions[0] * $dimensions[1] > self::MAX_PIXELS) {
            return;
        }

        $source = imagecreatefromstring((string) file_get_contents($path));

        if ($source === false) {
            return;
        }

        $keepsWebp = $media->mime_type === 'image/webp';
        ['bytes' => $bytes, 'width' => $width, 'height' => $height] = $this->compress($source, $keepsWebp);

        if ($media->mime_type === 'image/png') {
            $media->file_name = pathinfo($media->file_name, PATHINFO_FILENAME).'.jpg';
            $media->mime_type = 'image/jpeg';
        }

        file_put_contents($media->getPath(), $bytes);

        if ($media->getPath() !== $path) {
            unlink($path);
        }

        $media->size = strlen($bytes);
        $media->setCustomProperty('width', $width)
            ->setCustomProperty('height', $height)
            ->saveQuietly();
    }

    /**
     * Encodes the image at the highest quality and size that fit under the limit.
     *
     * @return array{bytes: string, width: int, height: int}
     */
    private function compress(GdImage $source, bool $asWebp): array
    {
        $sourceWidth = imagesx($source);
        $sourceHeight = imagesy($source);
        $longEdge = min(self::MAX_EDGE, max($sourceWidth, $sourceHeight));

        while (true) {
            $scale = $longEdge / max($sourceWidth, $sourceHeight);
            $width = max(1, (int) round($sourceWidth * $scale));
            $height = max(1, (int) round($sourceHeight * $scale));
            $canvas = $this->onWhiteCanvas($source, $width, $height);

            foreach (self::QUALITIES as $quality) {
                $result = ['bytes' => $this->encode($canvas, $asWebp, $quality), 'width' => $width, 'height' => $height];

                if (strlen($result['bytes']) <= self::MAX_BYTES || $longEdge <= self::MIN_EDGE) {
                    return $result;
                }
            }

            $longEdge = max(self::MIN_EDGE, (int) floor($longEdge * 0.85));
        }
    }

    /**
     * Resamples the image onto an opaque white canvas, so transparent PNG areas turn white in the JPEG.
     */
    private function onWhiteCanvas(GdImage $source, int $width, int $height): GdImage
    {
        $canvas = imagecreatetruecolor($width, $height);
        imagefill($canvas, 0, 0, (int) imagecolorallocate($canvas, 255, 255, 255));
        imagecopyresampled($canvas, $source, 0, 0, 0, 0, $width, $height, imagesx($source), imagesy($source));

        return $canvas;
    }

    private function encode(GdImage $image, bool $asWebp, int $quality): string
    {
        ob_start();

        if ($asWebp) {
            imagewebp($image, null, $quality);
        } else {
            imageinterlace($image, true);
            imagejpeg($image, null, $quality);
        }

        return (string) ob_get_clean();
    }
}
