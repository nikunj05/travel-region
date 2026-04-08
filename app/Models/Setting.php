<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Translatable\HasTranslations;

class Setting extends Model
{
    use HasTranslations;

    protected $fillable = [
        'logo',
        'footer_logo',
        'favicon',
        'header_menu_items',
        'footer_explore_items',
        'footer_about_items',
        'footer_support_items',
        'copyright',
        'footer_info',
        'contact_us',
        'whatsapp_number',
        'social_media_links',
        'home_title',
        'home_subtitle',
        'home_hero_content',
        'home_hero_image',
        'home_hero_image_ar',
        'home_hero_image_tablet',
        'home_hero_image_tablet_ar',
        'home_hero_image_mobile',
        'home_hero_image_mobile_ar',
        'five_star_commission',
        'four_star_commission',
        'three_star_commission',
        'two_star_commission',
        'one_star_commission',
        'faq_background_color',
    ];

    public $translatable = ['copyright', 'footer_info', 'home_title', 'home_subtitle', 'home_hero_content'];

    protected function casts(): array
    {
        return [
            'header_menu_items' => 'array',
            'footer_explore_items' => 'array',
            'footer_about_items' => 'array',
            'footer_support_items' => 'array',
            'social_media_links' => 'array',
            'copyright' => 'array',
            'footer_info' => 'array',
            'home_title' => 'array',
            'home_subtitle' => 'array',
            'faq_background_color' => 'array',
        ];
    }
}
