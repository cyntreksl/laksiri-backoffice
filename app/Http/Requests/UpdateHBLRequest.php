<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHBLRequest extends FormRequest
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
            'hbl_type' => ['required'],
            'hbl_name' => ['required'],
            'email' => ['email', 'max:254', 'nullable'],
            'contact_number' => ['nullable'],
            'additional_mobile_number' => ['nullable', 'phone:INTERNATIONAL'],
            'whatsapp_number' => ['nullable', 'phone:INTERNATIONAL'],
            'nic' => ['nullable'],
            'iq_number' => ['nullable'],
            'address' => ['nullable'],
            'consignee_name' => ['required'],
            'consignee_nic' => ['required'],
            'consignee_contact' => ['required'],
            'consignee_additional_mobile_number' => ['nullable', 'phone:INTERNATIONAL'],
            'consignee_whatsapp_number' => ['nullable', 'phone:INTERNATIONAL'],
            'consignee_address' => ['required'],
            'consignee_note' => ['nullable'],
            'warehouse' => ['required'],
            'freight_charge' => ['required', 'numeric'],
            'bill_charge' => ['required', 'numeric'],
            'other_charge' => ['required', 'numeric'],
            'destination_charge' => ['required', 'numeric'],
            'discount' => ['required', 'numeric'],
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'packages' => ['sometimes', 'required', 'array'],
        ];
    }
}
