<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Testimonial extends Model
{
    use HasTranslations;

    protected $fillable = [
        'name',
        'location',
        'photo',
        'message',
        'rating',
        'hotel',
        'stay_date',
    ];

    public $translatable = ['name', 'location', 'message', 'hotel'];

    protected $casts = [
        'name' => 'array',
        'location' => 'array',
        'message' => 'array',
        'hotel' => 'array',
    ];
}
