<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreMemberApplicationRequest;
use App\Models\MemberApplication;
use Illuminate\Http\JsonResponse;

class MemberApplicationController extends Controller
{
    /**
     * Store an application from the Studio > Join form for the admins to review.
     */
    public function store(StoreMemberApplicationRequest $request): JsonResponse
    {
        $application = MemberApplication::create(
            $request->safe()->only(['name', 'email', 'phone', 'city', 'discipline', 'portfolio_url', 'instagram', 'message']),
        );

        return response()->json(['data' => ['id' => $application->id]], 201);
    }
}
