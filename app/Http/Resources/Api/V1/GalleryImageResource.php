<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Project;
use Illuminate\Http\Request;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * An image in a designer's gallery, with the work it belongs to (needs `model.category` loaded).
 *
 * @mixin Media
 */
class GalleryImageResource extends MediaResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /** @var Project $work */
        $work = $this->model;

        return [
            ...parent::toArray($request),
            'work' => [
                'id' => $work->id,
                'slug' => $work->slug,
                'title' => $work->title,
                'year' => $work->year,
                'discipline' => $work->category->discipline->value,
                'category' => CategoryResource::make($work->category),
            ],
        ];
    }
}
