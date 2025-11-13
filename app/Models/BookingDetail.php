<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingDetail extends Model
{
    protected $fillable = [
        'booking_id',
        'room_code',
        'rate_key',
        'price_per_night',
        'first_name',
        'last_name',
        'email',
        'country',
        'country_code',
        'phone',
        'is_primary',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
