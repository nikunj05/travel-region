<?php

namespace App\Http\Resources;

use App\Helpers\TextSanitizer;
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
            'rate_class' => $this->rate_class,
            'room_name' => $this->room_name,
            'board_name' => $this->board_name,
            'amount' => $this->amount,
            'net_amount' => $this->net_amount,
            'net_currency' => $this->net_currency,
            'rate_comments' => TextSanitizer::sanitizeHotelDescription($this->rate_comments),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
