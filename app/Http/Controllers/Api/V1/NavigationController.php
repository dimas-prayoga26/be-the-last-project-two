<?php

namespace App\Http\Controllers\Api\V1;

use App\Enums\Discipline;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CategoryResource;
use App\Models\Category;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;

class NavigationController extends Controller
{
    /**
     * The Architecture / Interior / Design menus with their categories and a preview image each.
     */
    public function __invoke(): JsonResponse
    {
        $categories = Category::query()
            ->ordered()
            ->with(['projects' => fn (Relation $query) => $query
                ->published()
                ->ordered()
                ->limit(1)
                ->with(['media' => fn (Relation $media) => $media->where('collection_name', 'cover')]),
            ])
            ->get();

        return response()->json([
            'data' => collect(Discipline::cases())->map(fn (Discipline $discipline): array => [
                'key' => $discipline->value,
                'categories' => CategoryResource::collection(
                    $categories->where('discipline', $discipline)->values(),
                ),
            ]),
        ]);
    }
}
