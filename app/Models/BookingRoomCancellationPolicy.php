<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingRoomCancellationPolicy extends Model
{
    protected $fillable = [
        'booking_room_id',
        'amount',
        'from',
    ];

    public function bookingRoom()
    {
        return $this->belongsTo(BookingRoom::class);
    }
}
