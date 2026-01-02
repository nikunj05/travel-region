<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class SettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $hero_image = null;
        $hero_image_tablet = null;
        $hero_image_mobile = null;
        if (app()->getLocale() == 'ar') {
            $hero_image = $this->home_hero_image_ar ? url(Storage::url($this->home_hero_image_ar)) : null;
            $hero_image_tablet = $this->home_hero_image_tablet_ar ? url(Storage::url($this->home_hero_image_tablet_ar)) : null;
            $hero_image_mobile = $this->home_hero_image_mobile_ar ? url(Storage::url($this->home_hero_image_mobile_ar)) : null;
        }

        if (app()->getLocale() == 'en') {
            $hero_image = $this->home_hero_image ? url(Storage::url($this->home_hero_image)) : null;
            $hero_image_tablet = $this->home_hero_image_tablet ? url(Storage::url($this->home_hero_image_tablet)) : null;
            $hero_image_mobile = $this->home_hero_image_mobile ? url(Storage::url($this->home_hero_image_mobile)) : null;
        }

        return [
            'logo' => $this->logo ? url(Storage::url($this->logo)) : null,
            'favicon' => $this->favicon ? url(Storage::url($this->favicon)) : null,
            'footer_logo' => $this->footer_logo ? url(Storage::url($this->footer_logo)) : null,
            // 'header_menu_items' => $this->header_menu_items,
            // 'footer_explore_items' => $this->footer_explore_items,
            // 'footer_about_items' => $this->footer_about_items,
            // 'footer_support_items' => $this->footer_support_items,
            'copyright' => $this->copyright,
            'footer_info' => $this->footer_info,
            'contact_us' => $this->contact_us,
            'whatsapp_number' => $this->whatsapp_number,
            'home_title' => $this->home_title,
            'home_subtitle' => $this->home_subtitle,
            'home_hero_image' => $hero_image,
            'home_hero_image_tablet' => $hero_image_tablet,
            'home_hero_image_mobile' => $hero_image_mobile,
            'social_media_links' => collect($this->social_media_links)->map(function ($socialMedia) {
                return [
                    'title' => $socialMedia['title'],
                    'link'  => $socialMedia['link'],
                    'icon'  => isset($socialMedia['icon'])
                        ? url(Storage::url($socialMedia['icon']))
                        : null,
                ];
            })->toArray(),
            'faq_background_color' => $this->faq_background_color,
        ];
    }
}
