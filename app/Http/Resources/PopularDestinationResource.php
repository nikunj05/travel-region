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
            'code' => $this->location,
            'location' => $this->destination ? $this->destination->name : $this->location,
            'image' => $this->image,
            'full_image_url' => $this->image ? url(Storage::url($this->image)) : null,
            'hotel_count' => $this->hotel_count,
            'hotel_min_price' => $this->hotel_min_price,
        ];
    }
}
