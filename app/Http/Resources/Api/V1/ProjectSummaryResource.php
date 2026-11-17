<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Designer;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A project as shown on listing cards.
 *
 * @mixin Project
 */
class ProjectSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $cover = $this->getFirstMedia('cover');

        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'summary' => $this->summary,
            'year' => $this->year,
            'location' => $this->location,
            'is_featured' => $this->is_featured,
            'discipline' => $this->whenLoaded('category', fn (): string => $this->category->discipline->value),
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'cover' => $cover ? MediaResource::make($cover) : null,
            'designers' => $this->whenLoaded('designers', fn () => $this->designers->map(fn (Designer $designer): array => [
                'name' => $designer->name,
                'slug' => $designer->slug,
            ])),
        ];
    }
}
