<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * An uploaded image with the URLs of its generated conversions.
 *
 * @mixin Media
 */
class MediaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'url' => $this->conversionUrl('large') ?? $this->getUrl(),
            'original' => $this->getUrl(),
            'card' => $this->conversionUrl('card'),
            'thumb' => $this->conversionUrl('thumb'),
            'width' => $this->getCustomProperty('width'),
            'height' => $this->getCustomProperty('height'),
            'alt' => $this->getCustomProperty('alt'),
        ];
    }

    private function conversionUrl(string $conversion): ?string
    {
        return $this->hasGeneratedConversion($conversion) ? $this->getUrl($conversion) : null;
    }
}
