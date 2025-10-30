<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RateExchange extends Model
{
    protected $fillable = [
        'from_currency',
        'to_currency',
        'exchange_rate',
        'rate_date',
    ];
}
