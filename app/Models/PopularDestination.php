<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class PopularDestination extends Model
{
    use HasTranslations;

    protected $fillable = [
        'location',
        'image',
        'city',
        'state',
        'country',
        'latitude',
        'longitude',
        'hotel_count',
        'hotel_min_price',
    ];

    protected static function booted()
    {
        static::saving(function ($model) {
            // If location is not set but we have the components, build it
            if (!$model->location && $model->city && $model->country) {
                $parts = array_filter([$model->city, $model->state, $model->country]);
                $model->location = implode(', ', $parts);
            }
        });
    }
}
