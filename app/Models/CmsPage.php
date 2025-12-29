<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class CmsPage extends Model
{
    use HasTranslations;

    protected $fillable = [
        'slug',
        'title',
        'sub_title',
        'background_image',
        'content',
        'about_us',
        'founder_image',
        'founder_title',
        'founder_name',
        'founder_designation',
        'why_we_exist',
        'our_partners',
        'few_highlights',
        'ready_to_explore_title',
        'ready_to_explore_sub_title',
        'ready_to_explore_image'
    ];

    public $translatable = [
        'content',
        'founder_title',
        'founder_name',
        'founder_designation',
        'why_we_exist',
        'few_highlights',
        'ready_to_explore_title',
        'ready_to_explore_sub_title',
    ];

    protected $casts = [
        'content' => 'array',
        'about_us' => 'boolean',
        'why_we_exist' => 'array',
        'our_partners' => 'array',
        'few_highlights' => 'array',
        'founder_title' => 'array',
        'founder_name' => 'array',
        'founder_designation' => 'array',
    ];
}
