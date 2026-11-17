<?php

namespace App\Http\Resources\Api\V1;

use App\Enums\Discipline;
use App\Models\Designer;
use App\Models\Project;
use Illuminate\Http\Request;

/**
 * A designer profile page with bio, contact and portfolio.
 *
 * @mixin Designer
 */
class DesignerResource extends DesignerSummaryResource
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
            'email' => $this->email,
            'headline' => $this->headline,
            'bio' => $this->bio,
            'statement' => $this->statement,
            // The disciplines of the published works, in menu order (needs `projects.category` loaded).
            'disciplines' => $this->whenLoaded('projects', fn (): array => collect(Discipline::cases())
                ->filter(fn (Discipline $discipline): bool => $this->projects->contains(fn (Project $project): bool => $project->category->discipline === $discipline))
                ->map(fn (Discipline $discipline): string => $discipline->value)
                ->values()
                ->all()),
            'projects' => ProjectSummaryResource::collection($this->whenLoaded('projects')),
        ];
    }
}
