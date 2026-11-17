<?php

namespace App\Listeners;

use Spatie\MediaLibrary\MediaCollections\Events\MediaHasBeenAddedEvent;

/**
 * Saves the pixel size of every uploaded image so the frontend can lay out
 * galleries (landscape rows vs portrait pairs) and reserve space before load.
 */
class StoreMediaDimensions
{
    public function handle(MediaHasBeenAddedEvent $event): void
    {
        $media = $event->media;

        if (! str_starts_with((string) $media->mime_type, 'image/') || ! is_file($media->getPath())) {
            return;
        }

        $size = getimagesize($media->getPath());

        if ($size === false) {
            return;
        }

        $media->setCustomProperty('width', $size[0])
            ->setCustomProperty('height', $size[1])
            ->saveQuietly();
    }
}
