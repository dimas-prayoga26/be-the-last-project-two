<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Project;
use Illuminate\Http\Request;

/**
 * A project detail page with its full credits and gallery.
 *
 * @mixin Project
 */
class ProjectResource extends ProjectSummaryResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            ...parent::toArray($request),
            'description' => $this->description,
            'client' => $this->client,
            'area' => $this->area,
            'photographer' => $this->photographer,
            'published_at' => $this->published_at?->toIso8601String(),
            'designers' => DesignerSummaryResource::collection($this->whenLoaded('designers')),
            'gallery' => MediaResource::collection($this->getMedia('gallery')),
        ];
    }
}
