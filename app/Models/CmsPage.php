<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class CmsPage extends Model
{
    use HasTranslations;

    protected $fillable = ['slug', 'title', 'content'];

    public $translatable = ['content'];

    protected $casts = [
        'content' => 'array',
    ];
}
