<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'coupon_id',
        'booking_id',
        'amount',
        'currency',
        'status',
        'tap_response',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
