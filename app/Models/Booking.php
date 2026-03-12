<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'user_id',
        'hotel_code',
        'category',
        'accommodation_type',
        'address',
        'phone',
        'order',
        'booking_reference',
        'supplier_name',
        'vat_number',
        'check_in',
        'check_out',
        'rooms',
        'adults',
        'children',
        'child_age',
        'nights',
        'total_price',
        'discount_amount',
        'refunded_amount',
        'refunded_currency',
        'tap_refund_id',
        'currency',
        'net_total_price',
        'net_currency',
        'status',
        'payment_status',
        'special_requests',
        'cancellation_in_progress',
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

    public function primary_details()
    {
        return $this->hasOne(BookingDetail::class)->where('is_primary', 1);
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
