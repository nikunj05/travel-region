<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Testimonial extends Model
{
    protected $fillable = [
        'name',
        'location',
        'photo',
        'message',
        'rating',
        'hotel',
        'stay_date',
    ];
}
