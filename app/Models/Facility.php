<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Facility extends Model
{
    protected $fillable = [
        'code',
        'name',
        'language_code',
        'facility_group_code',
    ];
}
