<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'min:5', 'max:20'],
            'username' => ['required', 'unique:users,username', 'min:5', 'max:20'],
            'email' => ['nullable', 'email', 'max:254', 'unique:users'],
            'password' => ['required', 'string', Password::default(), 'confirmed'],
            'primary_branch_id' => ['required', 'exists:branches,id'],
            'secondary_branches' => ['nullable'],
            'role' => ['required', 'exists:roles,name'],
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'primary_branch_id.required' => 'The primary branch is required.',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
