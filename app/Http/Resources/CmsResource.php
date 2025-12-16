<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CmsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            "title" => $this->title,
            "sub_title" => $this->sub_title,
            "background_image" => $this->background_image,
            "background_image_url" => $this->background_image ? url(Storage::url($this->background_image)) : null,
            "slug" => $this->slug,
            "content" => $this->content,
            "about_us" => (bool) $this->about_us,
            "founder_image" => $this->founder_image,
            "founder_image_url" => $this->founder_image ? url(Storage::url($this->founder_image)) : null,
            "why_we_exist" => $this->why_we_exist ? collect($this->why_we_exist)->filter(function ($item) { return $item['title'][app()->getLocale()] && $item['description'][app()->getLocale()]; })->map(function ($item) {
                return [
                    'title' => $item['title'] ? $item['title'][app()->getLocale()] : null,
                    'description' => $item['description'] ? $item['description'][app()->getLocale()] : null,
                    'icon' => $item['icon'] ?? null,
                    'icon_url' => isset($item['icon']) ? url(Storage::url($item['icon'])) : null,
                ];
            })->values() : null,
            "our_partners" => $this->our_partners ? collect($this->our_partners)->map(function ($item) {
                return isset($item) ? url(Storage::url($item)) : null;
            })->values() : null,
            "few_highlights" => $this->few_highlights ? collect($this->few_highlights)->filter(function ($item) { return $item['title'][app()->getLocale()] && $item['description'][app()->getLocale()]; })->map(function ($item) {
                return [
                    'title' => $item['title'] ? $item['title'][app()->getLocale()] : null,
                    'description' => $item['description'] ? $item['description'][app()->getLocale()] : null,
                    'icon' => $item['icon'] ?? null,
                    'icon_url' => isset($item['icon']) ? url(Storage::url($item['icon'])) : null,
                ];
            })->values() : null,
            "ready_to_explore_title" => $this->ready_to_explore_title,
            "ready_to_explore_sub_title" => $this->ready_to_explore_sub_title,
            "ready_to_explore_image" => $this->ready_to_explore_image,
            "ready_to_explore_image_url" => $this->ready_to_explore_image ? url(Storage::url($this->ready_to_explore_image)) : null,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at
        ];
    }
}
