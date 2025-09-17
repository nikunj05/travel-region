<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
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
    ];

    protected function casts(): array
    {
        return [
            'header_menu_items' => 'array',
            'footer_explore_items' => 'array',
            'footer_about_items' => 'array',
            'footer_support_items' => 'array',
        ];
    }
}
