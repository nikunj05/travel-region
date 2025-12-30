<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarkupHotel extends Model
{
    protected $fillable = [
        'hotel_code',
        'hotel_name',
        'markup_percentage',
    ];
}
