<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContainerRequest extends FormRequest
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
            'cargo_type' => ['required'],
            'container_type' => ['required_if:cargo_type,Sea Cargo'],
            'reference' => ['required', 'unique:containers,reference'],
            'bl_number' => ['required_if:cargo_type,Sea Cargo'],
            'awb_number' => ['required_if:cargo_type,Air Cargo'],
            'container_number' => ['required_if:cargo_type,Sea Cargo'],
        ];
    }
}
