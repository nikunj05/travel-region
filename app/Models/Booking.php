<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'hotel_code',
        'order',
        'check_in',
        'check_out',
        'rooms',
        'adults',
        'children',
        'nights',
        'total_price',
        'currency',
        'status',
        'coupon_id',
        'tap_response',
    ];

    protected $casts = [
        'check_in' => 'date',
        'check_out' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function details()
    {
        return $this->hasMany(BookingDetail::class);
    }

    public function booking_room()
    {
        return $this->hasMany(BookingRoom::class);
    }
}
