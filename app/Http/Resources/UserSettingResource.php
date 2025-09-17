<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserSettingResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'language' => $this->language,
            'currency' => $this->currency,
            'email' => $this->email,
            'country_code' => $this->country_code,
            'mobile' => $this->mobile,
        ];
    }
}
