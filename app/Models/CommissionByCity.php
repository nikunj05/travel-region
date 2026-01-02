<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CommissionByCity extends Model
{
    protected $fillable = [
        'city',
        'commission_percentage',
    ];
}
