<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FavoriteHotel extends Model
{
    protected $fillable = [
        'user_id',
        'hotel_codes',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
