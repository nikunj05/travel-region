<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class UserSettingRequest extends FormRequest
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
            'language' => 'required|string|max:10',
            'currency' => 'required|string|max:10',
            'email' => 'required|email|max:255|unique:users,email,' . $this->user()->id,
            'country_code' => 'required|string|max:10',
            'mobile' => 'required|string|max:10',
            'password' => [
                Password::min(8)
                    ->mixedCase() // Require at least one uppercase and one lowercase letter...
                    ->numbers(), // Require at least one number...
            ],
        ];
    }
}
