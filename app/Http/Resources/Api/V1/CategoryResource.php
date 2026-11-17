<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Category
 */
class CategoryResource extends JsonResource
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
            'discipline' => $this->discipline->value,
            'name' => $this->name,
            'slug' => $this->slug,
            // The latest project cover, shown as the menu preview image (needs `projects` loaded).
            'preview' => $this->whenLoaded('projects', function (): ?MediaResource {
                $cover = $this->projects->first()?->getFirstMedia('cover');

                return $cover ? MediaResource::make($cover) : null;
            }),
        ];
    }
}
