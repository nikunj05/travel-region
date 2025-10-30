<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PopularDestinationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'location' => $this->location,
            'image' => $this->image,
            'full_image_url' => $this->image ? url(Storage::url($this->image)) : null,
            'city' => $this->city,
            'state' => $this->state,
            'country' => $this->country,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
        ];
    }
}
