<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCourierRequest extends FormRequest
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
        ];
    }
}
