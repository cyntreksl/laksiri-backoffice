<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCourierStatusRequest extends FormRequest
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
            'couriers' => ['required', 'array', 'min:1'],
            'couriers.*' => ['required', 'integer', 'exists:couriers,id'],
            'status' => ['required', 'string', 'in:pending,on courier,delivered'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'couriers.required' => 'Please select at least one courier.',
            'couriers.array' => 'Invalid courier selection format.',
            'couriers.min' => 'Please select at least one courier.',
            'couriers.*.exists' => 'One or more selected couriers do not exist.',
            'status.required' => 'Please select a status.',
            'status.in' => 'Invalid status selected. Please choose from: pending, on courier, or delivered.',
        ];
    }
}
