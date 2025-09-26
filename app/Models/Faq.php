<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Faq extends Model
{
    use HasTranslations;

    protected $fillable = ['faq_category_id', 'question', 'answer'];

    public $translatable = ['question', 'answer'];

    protected $casts = [
        'question' => 'array',
        'answer' => 'array',
    ];

    public function category()
    {
        return $this->belongsTo(FaqCategory::class, 'faq_category_id');
    }
}
