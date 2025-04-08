<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePickupTypeRequest extends FormRequest
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
            'pickup_type_name' => ['required', 'string', 'unique:pickup_types,pickup_type_name'],
        ];
    }

    public function messages(): array
    {
        return [
            'pickup_type_name.required' => 'The pickup type name is required.',
            'pickup_type_name.string' => 'The pickup type name must be a valid string.',
            'pickup_type_name.unique' => 'This pickup type name is already in use. Please choose a different name.',
        ];
    }
}
