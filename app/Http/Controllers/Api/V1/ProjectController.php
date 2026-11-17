<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Discipline;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\ProjectResource;
use App\Http\Resources\Api\V1\ProjectSummaryResource;
use App\Models\Project;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class ProjectController extends Controller
{
    /**
     * Published projects, filterable by discipline, category, designer and featured flag.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'discipline' => ['nullable', Rule::enum(Discipline::class)],
            'category' => ['nullable', 'string', 'max:100'],
            'designer' => ['nullable', 'string', 'max:150'],
            'featured' => ['nullable', 'boolean'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:48'],
        ]);

        $projects = Project::query()
            ->published()
            ->when($request->enum('discipline', Discipline::class), fn (Builder $query, Discipline $discipline) => $query->inDiscipline($discipline))
            ->when($filters['category'] ?? null, fn (Builder $query, string $slug) => $query->whereHas('category', fn (Builder $category) => $category->where('slug', $slug)))
            ->when($filters['designer'] ?? null, fn (Builder $query, string $slug) => $query->whereHas('designers', fn (Builder $designer) => $designer->published()->where('slug', $slug)))
            ->when($request->boolean('featured'), fn (Builder $query) => $query->where('is_featured', true))
            ->with($this->cardRelations())
            ->ordered()
            ->paginate($filters['per_page'] ?? 12)
            ->withQueryString();

        return ProjectSummaryResource::collection($projects);
    }

    /**
     * A published project with its neighbours in the same discipline and related works.
     */
    public function show(Project $project): ProjectResource
    {
        abort_unless($project->isPublished(), 404);

        $project->load([
            'category',
            'designers' => fn (Relation $query) => $query->published()->ordered()->with('media'),
            'media',
        ]);

        [$previous, $next] = $this->neighbours($project);

        return ProjectResource::make($project)->additional([
            'previous' => $previous ? ProjectSummaryResource::make($previous) : null,
            'next' => $next ? ProjectSummaryResource::make($next) : null,
            'related' => ProjectSummaryResource::collection($this->related($project)),
        ]);
    }

    /**
     * The relations a project card needs, with only the cover image loaded.
     *
     * @return array<int|string, mixed>
     */
    private function cardRelations(): array
    {
        return [
            'category',
            'designers' => fn (Relation $query) => $query->published()->ordered(),
            'media' => fn (Relation $query) => $query->where('collection_name', 'cover'),
        ];
    }

    /**
     * The previous and next project in the same discipline, wrapping around at the ends.
     *
     * @return array{0: ?Project, 1: ?Project}
     */
    private function neighbours(Project $project): array
    {
        $ids = Project::query()
            ->published()
            ->inDiscipline($project->category->discipline)
            ->ordered()
            ->pluck('id');

        $position = $ids->search($project->id);

        if ($position === false || $ids->count() < 2) {
            return [null, null];
        }

        $previousId = $ids[($position - 1 + $ids->count()) % $ids->count()];
        $nextId = $ids[($position + 1) % $ids->count()];

        $neighbours = Project::query()
            ->whereKey([$previousId, $nextId])
            ->with($this->cardRelations())
            ->get()
            ->keyBy('id');

        return [$neighbours->get($previousId), $neighbours->get($nextId)];
    }

    /**
     * Up to four other works by the same designers, or from the same category when there are none.
     *
     * @return Collection<int, Project>
     */
    private function related(Project $project): Collection
    {
        $relatedQuery = fn () => Project::query()
            ->published()
            ->whereKeyNot($project->id)
            ->with($this->cardRelations())
            ->ordered()
            ->limit(4);

        $related = $relatedQuery()
            ->whereHas('designers', fn (Builder $query) => $query->whereKey($project->designers->modelKeys()))
            ->get();

        if ($related->isNotEmpty()) {
            return $related;
        }

        return $relatedQuery()->whereBelongsTo($project->category)->get();
    }
}
