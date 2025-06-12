<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCourierRequest extends FormRequest
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
            'hbl_type' => ['required'],
            'courier_agent' => ['required'],
            'email' => ['nullable', 'email', 'max:254'],
            'name' => ['required'],
            'contact_number' => ['phone:INTERNATIONAL'],
            'nic' => ['nullable'],
            'iq_number' => ['nullable'],
            'address' => ['nullable'],
            'consignee_name' => ['required'],
            'consignee_nic' => ['required'],
            'consignee_contact' => ['required', 'phone:INTERNATIONAL'],
            'consignee_address' => ['required'],
            'consignee_note' => ['nullable'],
            'amount' => ['nullable', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_amount' => ['nullable', 'numeric', 'min:0'],
            'tax_method' => ['nullable'],
            'tax_value' => ['nullable', 'numeric', 'min:0'],
            'discount_method' => ['nullable'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],

        ];
    }
}
