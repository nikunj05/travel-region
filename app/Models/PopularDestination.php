<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class PopularDestination extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name',
        'image',
        'city',
        'state',
        'country',
        'latitude',
        'longitude',
    ];
}
