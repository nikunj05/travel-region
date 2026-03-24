<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DestinationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $matchedName = $this->when(
            isset($this->matched_name),
            $this->matched_name,
            app()->getLocale() === 'ar' && $this->name_ar ? $this->name_ar : $this->name
        );

        return [
            'id' => $this->id,
            'code' => $this->code,
            'name' => $matchedName,
            'name_ar' => $this->name_ar,
            'country_code' => $this->country_code,
            'iso_code' => $this->iso_code,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
