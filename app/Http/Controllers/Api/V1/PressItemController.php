<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PressItemResource;
use App\Models\PressItem;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class PressItemController extends Controller
{
    /**
     * Published press items, newest first.
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate([
            'per_page' => ['nullable', 'integer', 'min:1', 'max:48'],
        ]);

        $pressItems = PressItem::query()
            ->published()
            ->latest('published_on')
            ->with('media')
            ->paginate($filters['per_page'] ?? 12)
            ->withQueryString();

        return PressItemResource::collection($pressItems);
    }
}
