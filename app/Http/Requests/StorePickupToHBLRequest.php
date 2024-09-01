<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePickupToHBLRequest extends FormRequest
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
            'hbl_type' => ['nullable'],
            'nic' => ['nullable'],
            'iq_number' => ['nullable'],
            'consignee_name' => ['nullable'],
            'consignee_nic' => ['nullable'],
            'consignee_contact' => ['nullable'],
            'consignee_address' => ['nullable'],
            'consignee_note' => ['nullable'],
            'warehouse' => ['nullable'],
            'freight_charge' => ['nullable', 'numeric'],
            'bill_charge' => ['nullable', 'numeric'],
            'other_charge' => ['nullable', 'numeric'],
            'discount' => ['nullable', 'numeric'],
            'paid_amount' => ['nullable', 'numeric'],
        ];
    }
}
