<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zone extends Model
{
    protected $fillable = [
        'destination_code',
        'code',
        'name',
        'name_ar',
    ];
}
