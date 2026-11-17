<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Designer;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * A designer as shown on cards and in credits.
 *
 * @mixin Designer
 */
class DesignerSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $photo = $this->getFirstMedia('photo');

        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'title' => $this->title,
            'location' => $this->location,
            'photo' => $photo ? MediaResource::make($photo) : null,
            'socials' => $this->socials ?? [],
            'projects_count' => $this->whenCounted('projects'),
        ];
    }
}
