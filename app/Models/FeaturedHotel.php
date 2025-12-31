<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FeaturedHotel extends Model
{
    protected $fillable = [
        'hotel_code',
        'hotel_name',
        'show_tag',
    ];
}
