<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactMessageRequest;
use App\Models\ContactMessage;
use Illuminate\Http\JsonResponse;

class ContactMessageController extends Controller
{
    /**
     * Store a message from the Studio > Contact form for the admins to read.
     */
    public function store(StoreContactMessageRequest $request): JsonResponse
    {
        $message = ContactMessage::create(
            $request->safe()->only(['name', 'email', 'phone', 'subject', 'message']),
        );

        return response()->json(['data' => ['id' => $message->id]], 201);
    }
}
