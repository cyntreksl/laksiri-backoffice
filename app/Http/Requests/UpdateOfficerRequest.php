<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOfficerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string'],
            'mobile_number' => ['required', 'phone:INTERNATIONAL'],
            'email' => ['nullable', 'email', Rule::unique('officers')->ignore($this->id)],
            'pp_or_nic_no' => ['required', 'max:254'],
            'residency_no' => ['required_if:type,shipper'],
            'address' => ['required'],
            'type' => ['required'],
        ];
    }
}
