<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Board extends Model
{
    protected $fillable = [
        'code',
        'name',
        'multi_lingual_code',
    ];
}
