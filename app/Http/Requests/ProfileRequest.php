<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProfileRequest extends FormRequest
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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            // 'email' => 'required|email|max:255|unique:users,email,' . $this->user()->id,
            'gender' => 'required|string|in:male,female',
            'country_code' => 'required|string|max:10',
            'mobile' => 'required|string|max:20|unique:users,mobile,' . $this->user()->id,
            'date_of_birth' => 'required|date|before:today',
            'nationality' => 'required|string|max:100',
            'address' => 'nullable|string|max:255',
            'passport_number' => 'nullable|string|max:20',
        ];
    }
}
