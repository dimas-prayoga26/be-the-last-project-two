<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\DesignerResource;
use App\Http\Resources\Api\V1\DesignerSummaryResource;
use App\Models\Designer;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class DesignerController extends Controller
{
    /**
     * All published designers in their display order.
     */
    public function index(): AnonymousResourceCollection
    {
        $designers = Designer::query()
            ->published()
            ->ordered()
            ->with('media')
            ->withCount(['projects' => fn (Builder $query) => $query->published()])
            ->get();

        return DesignerSummaryResource::collection($designers);
    }

    /**
     * A published designer with their portfolio.
     */
    public function show(Designer $designer): DesignerResource
    {
        abort_unless($designer->is_published, 404);

        $designer->load([
            'media',
            'projects' => fn (Relation $query) => $query
                ->published()
                ->ordered()
                ->with([
                    'category',
                    'designers' => fn (Relation $designers) => $designers->published()->ordered(),
                    'media' => fn (Relation $media) => $media->where('collection_name', 'cover'),
                ]),
        ]);

        return DesignerResource::make($designer);
    }
}
