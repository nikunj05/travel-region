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
        'rate_class',
        'amount',
        'net_amount',
        'net_currency',
        'rate_comments',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function cancellation_policies()
    {
        return $this->hasMany(BookingRoomCancellationPolicy::class);
    }

    public function guest()
    {
        return $this->belongsTo(BookingDetail::class, 'room_code', 'room_code');
    }
}
