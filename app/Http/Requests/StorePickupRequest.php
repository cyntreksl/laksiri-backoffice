<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePickupRequest extends FormRequest
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
            'name' => ['required'],
            'email' => ['required', 'email', 'max:254'],
            'contact_number' => ['required'],
            'address' => ['required'],
            'location' => ['nullable'],
            'zone_id' => ['required', 'integer'],
            'notes' => ['nullable'],
        ];
    }
}
