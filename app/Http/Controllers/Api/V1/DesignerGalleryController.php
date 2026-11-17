<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Discipline;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\GalleryImageResource;
use App\Models\Designer;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class DesignerGalleryController extends Controller
{
    /**
     * Every image of a designer's published works, filterable by discipline and work.
     *
     * Works keep their portfolio order; within a work the cover comes before the gallery.
     */
    public function __invoke(Request $request, Designer $designer): AnonymousResourceCollection
    {
        abort_unless($designer->is_published, 404);

        $filters = $request->validate([
            'discipline' => ['nullable', Rule::enum(Discipline::class)],
            'work' => ['nullable', 'string', 'max:150'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:60'],
        ]);

        $works = Project::query()
            ->published()
            ->whereHas('designers', fn (Builder $query) => $query->whereKey($designer->getKey()))
            ->when($request->enum('discipline', Discipline::class), fn (Builder $query, Discipline $discipline) => $query->inDiscipline($discipline))
            ->when($filters['work'] ?? null, fn (Builder $query, string $slug) => $query->where('slug', $slug))
            ->select('id');

        $images = Media::query()
            ->select('media.*')
            ->join('projects', 'projects.id', '=', 'media.model_id')
            ->where('media.model_type', (new Project)->getMorphClass())
            ->whereIn('media.collection_name', ['cover', 'gallery'])
            ->whereIn('media.model_id', $works)
            ->orderBy('projects.sort_order')
            ->orderByDesc('projects.published_at')
            ->orderByDesc('projects.id')
            ->orderByRaw("case when media.collection_name = 'cover' then 0 else 1 end")
            ->orderBy('media.order_column')
            ->with('model.category')
            ->paginate($filters['per_page'] ?? 24)
            ->withQueryString();

        return GalleryImageResource::collection($images);
    }
}
