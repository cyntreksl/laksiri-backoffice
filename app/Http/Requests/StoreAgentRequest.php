<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreAgentRequest extends FormRequest
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
            'name' => ['required'],
            'branch_code' => ['required', 'unique:branches,branch_code', 'max:10'],
            'type' => ['required'],
            'currency_name' => ['required'],
            'currency_symbol' => ['required', 'max:3'],
            'cargo_modes' => ['required'],
            'delivery_types' => ['required'],
            'package_types' => ['required'],
            'is_third_party_agent' => ['nullable'],
        ];
    }
}
// Compare this snippet from app/Http/Requests/StoreAgentRequest.php:
