<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HotelFacility extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'hotel_code',
        'facility_code',
        'facility_group_code',
    ];
}
