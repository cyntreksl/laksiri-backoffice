<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreDriverRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'min:5', 'max:20'],
            'username' => ['required', 'unique:users,username', 'min:5', 'max:20'],
            'password' => ['required', 'string', Password::default(), 'confirmed'],
            'contact' => ['required'],
            'working_hours_start' => ['nullable'],
            'working_hours_end' => ['nullable'],
            'preferred_zone' => ['nullable'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'contact' => 'mobile number',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
