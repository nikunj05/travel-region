<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Destination extends Model
{
    protected $fillable = [
        'code',
        'name',
        'country_code',
        'iso_code',
    ];
}
