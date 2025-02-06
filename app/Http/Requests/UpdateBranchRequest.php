<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBranchRequest extends FormRequest
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
            'name' => ['required'],
            'branch_code' => [
                'required',
                'max:10',
                Rule::unique('branches', 'branch_code')->ignore($this->route('branch')),
            ],
            'type' => ['required'],
            'currency_name' => ['required'],
            'currency_symbol' => ['required', 'max:3'],
            'country_code' => ['required', 'max:5'],
            'country' => ['required', 'max:30'],
            'cargo_modes' => ['required'],
            'delivery_types' => ['required'],
            'package_types' => ['required'],
            'email' => ['nullable', 'email', 'string'],
            'container_delays' => ['required', 'integer'],
        ];
    }
}
