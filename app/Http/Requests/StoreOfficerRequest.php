<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreOfficerRequest extends FormRequest
{
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
            'name' => ['required', 'string'],
            'email' => [
                'nullable',
                'email',
                'unique:officers,email',
                'max:254',
                'required_if:type,shipper',
            ],
            'mobile_number' => ['required', 'phone:INTERNATIONAL'],
            'pp_or_nic_no' => ['required_if:type,shipper', 'max:254'],
            'residency_no' => ['required_if:type,shipper'],
            'address' => ['required'],
            'type' => ['required'],
        ];
    }
}
