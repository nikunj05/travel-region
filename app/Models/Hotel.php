<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Hotel extends Model
{
    protected $fillable = [
        'code',
        'name',
        'longitude',
        'latitude',
        'destination_code',
        'category_code',
        'category_group_code',
        'accommodation_type_code',
        'city',
        'address',
        'zone_code',
        'chain_code',
        'status',
    ];

    public function first_image()
    {
        return $this->hasOne(HotelImage::class, 'hotel_code', 'code');
    }

    public function facilities()
    {
        return $this->hasMany(HotelFacility::class, 'hotel_code', 'code');
    }

    public function images()
    {
        return $this->hasMany(HotelImage::class, 'hotel_code', 'code');
    }
}
