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
        return [
            'logo' => $this->logo ? url(Storage::url($this->logo)) : null,
            'favicon' => $this->favicon ? url(Storage::url($this->favicon)) : null,
            'footer_logo' => $this->footer_logo ? url(Storage::url($this->footer_logo)) : null,
            'header_menu_items' => $this->header_menu_items,
            'footer_explore_items' => $this->footer_explore_items,
            'footer_about_items' => $this->footer_about_items,
            'footer_support_items' => $this->footer_support_items,
            'copyright' => $this->copyright,
            'footer_info' => $this->footer_info,
        ];
    }
}
