<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Blog extends Model
{
    use HasTranslations;

    protected $fillable = [
        'category_id',
        'title',
        'content',
        'image',
        'read_time',
        'is_featured',
        'tags',
        'author',
        'author_image',
    ];

    public $translatable = [
        'title',
        'content',
    ];

    protected $casts = [
        'tags' => 'array',
        'title' => 'array',
        'content' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function comments()
    {
        return $this->hasMany(BlogComment::class)->latest();
    }
}
