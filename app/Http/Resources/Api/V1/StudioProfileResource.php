<?php

namespace App\Http\Resources\Api\V1;

use App\Models\StudioProfile;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * Content for the Studio pages (About, Contact, Follow) and the site footer.
 *
 * @mixin StudioProfile
 */
class StudioProfileResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $aboutImage = $this->getFirstMedia('about_image');

        return [
            'intro' => $this->intro,
            'about' => [
                'heading' => $this->about_heading,
                'body' => $this->about_body,
                'image' => $aboutImage ? MediaResource::make($aboutImage) : null,
            ],
            'contact' => [
                'email' => $this->email,
                'phone' => $this->phone,
                'whatsapp' => $this->whatsapp,
                'address' => $this->address,
                'map_url' => $this->map_url,
                'office_hours' => $this->office_hours,
            ],
            'socials' => $this->socials ?? [],
        ];
    }
}
