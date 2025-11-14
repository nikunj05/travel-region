<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingRoom extends Model
{
    protected $fillable = [
        'booking_id',
        'room_code',
        'rate_key',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
