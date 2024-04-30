<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'username' => ['required', 'unique:users,username', 'min:5', 'max:20'],
            'email' => ['nullable', 'email', 'max:254', 'unique:users'],
            'password' => ['required', 'string', Password::default(), 'confirmed'],
            'primary_branch_id' => ['required', 'exists:branches,id'],
            'secondary_branches' => ['nullable'],
            'role_id' => ['required', 'exists:roles,id'],
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
