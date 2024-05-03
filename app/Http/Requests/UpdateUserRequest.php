<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'min:5', 'max:20'],
            'username' => ['required', 'min:5', 'max:20',
                Rule::unique('users', 'username')->ignore($this->user->id),
            ],
            'email' => ['nullable', 'email', 'max:254',
                Rule::unique('users', 'email')->ignore($this->user->id),
            ],
        ];
    }
}
