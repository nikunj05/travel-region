<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingRoomResource extends JsonResource
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
            'booking_id' => $this->booking_id,
            'room_code' => $this->room_code,
            'rate_key' => $this->rate_key,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
