<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
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
            'user_id' => $this->user_id,
            'hotel_code' => $this->hotel_code,
            'check_in' => $this->check_in,
            'check_out' => $this->check_out,
            'hotel_name' => $this->hotel_name,
            'hotel_location' => $this->hotel_location,
            'hotel_images' => $this->hotel_images ? explode(',', $this->hotel_images) : [],
            'rooms' => $this->rooms,
            'adults' => $this->adults,
            'children' => $this->children,
            'nights' => $this->nights,
            'total_price' => $this->total_price,
            'discount_amount' => $this->discount_amount,
            'currency' => $this->currency,
            'order' => $this->order,
            'status' => $this->status,
            'payment_status' => $this->payment_status,
            'special_requests' => $this->special_requests,
            'child_age' => $this->child_age ? json_decode($this->child_age) : null,
            'coupon_id' => $this->coupon_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'details' => BookingDetailResource::collection($this->details),
            'room_details' => BookingRoomResource::collection($this->booking_room),
            'coupon' => $this->coupon ? new CouponResource($this->coupon) : null,
        ];
    }
}
