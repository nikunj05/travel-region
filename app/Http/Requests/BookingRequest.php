<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BookingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'hotel_code' => 'required|max:15',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'rooms' => 'required|integer|min:1',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'currency' => 'required|string|max:10',
            'special_requests' => 'nullable|string',
            'details' => 'required|array',
            'details.*.room_code' => 'required|string|max:15',
            'details.*.nights' => 'nullable|integer|min:1',
            'details.*.price_per_night' => 'required|numeric|min:0',
            'details.*.first_name' => 'nullable|string|max:255',
            'details.*.last_name' => 'nullable|string|max:255',
            'details.*.email' => 'nullable|email|max:255',
            'details.*.country' => 'nullable|string|max:255',
            'details.*.country_code' => 'nullable|string|max:10',
            'details.*.phone' => 'nullable|string|max:20',
        ];
    }
}
