<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\StudioProfileResource;
use App\Models\StudioProfile;

class StudioProfileController extends Controller
{
    /**
     * The studio's About, Contact and Follow content (empty until an admin fills it in).
     */
    public function __invoke(): StudioProfileResource
    {
        $studio = StudioProfile::query()->oldest('id')->with('media')->first() ?? new StudioProfile;

        return StudioProfileResource::make($studio);
    }
}
