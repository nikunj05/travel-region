<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    protected $fillable = [
        'code',
        'name',
        'name_ar',
        'country_code',
        'iso_code',
    ];
}
