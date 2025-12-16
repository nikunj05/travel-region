<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingRoom extends Model
{
    protected $fillable = [
        'booking_id',
        'room_code',
        'room_name',
        'board_name',
        'rate_key',
        'amount',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
