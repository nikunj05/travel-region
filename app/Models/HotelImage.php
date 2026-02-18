<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelImage extends Model
{
    protected $fillable = [
        'hotel_code',
        'path',
        'image_type_code',
        'order',
        'visual_order',
        'characteristic_code',
        'room_code',
        'room_type',
    ];
}
