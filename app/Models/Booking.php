<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'hotel_code',
        'order',
        'booking_reference',
        'check_in',
        'check_out',
        'rooms',
        'adults',
        'children',
        'nights',
        'total_price',
        'discount_amount',
        'currency',
        'status',
        'coupon_id',
        'tap_response',
        'tap_charge_id',
        'hotel_name',
        'hotel_location',
        'hotel_images'
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

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }
}
