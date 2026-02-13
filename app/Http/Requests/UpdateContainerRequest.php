<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateContainerRequest extends FormRequest
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
            'cargo_type' => ['required'],
            'container_type' => ['required'],
            'reference' => [
                'required',
                Rule::unique('containers', 'reference')->ignore($this->route('container')->id),
            ],
            'bl_number' => ['required_if:cargo_type,Sea Cargo'],
            'awb_number' => ['required_if:cargo_type,Air Cargo'],
            'container_number' => ['required_if:cargo_type,Sea Cargo'],
            'airline_name' => ['required_if:cargo_type,Air Cargo'],
            'shipment_weight' => ['nullable', 'numeric', 'min:0'],
            'arrived_at_primary_warehouse' => ['nullable', 'date'],
        ];
    }
}
