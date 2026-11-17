<?php

namespace App\Http\Resources\Api\V1;

use App\Models\PressItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin PressItem
 */
class PressItemResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $image = $this->getFirstMedia('image');

        return [
            'id' => $this->id,
            'title' => $this->title,
            'publication' => $this->publication,
            'published_on' => $this->published_on->toDateString(),
            'url' => $this->url,
            'excerpt' => $this->excerpt,
            'image' => $image ? MediaResource::make($image) : null,
            'document_url' => $this->getFirstMediaUrl('document') ?: null,
        ];
    }
}
