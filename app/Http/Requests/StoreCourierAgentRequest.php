<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCourierAgentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'company_name' => ['unique:courier_agents', 'required', 'string', 'max:254'],
            'website' => ['nullable', 'url', 'max:254'],
            'contact_number_1' => ['required', 'phone:INTERNATIONAL'],
            'contact_number_2' => ['nullable', 'phone:INTERNATIONAL'],
            'email' => ['nullable', 'email', 'max:254'],
            'address' => ['required', 'max:254'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'invoice_header' => ['nullable', 'string', 'max:254'],
            'invoice_footer' => ['nullable', 'string', 'max:254'],

        ];

    }
}
