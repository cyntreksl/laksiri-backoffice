<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePickupTypeRequest extends FormRequest
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
            'pickup_type_name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('pickup_types')->ignore($this->pickup_type->id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'pickup_type_name.required' => 'The Pickup Type Name is required.',
            'pickup_type_name.string' => 'The Pickup Type Name must be a valid string.',
            'pickup_type_name.max' => 'The Pickup Type Name cannot exceed 255 characters.',
            'pickup_type_name.unique' => 'This Pickup Type Name is already taken. Please choose a different name.',
        ];
    }
}
