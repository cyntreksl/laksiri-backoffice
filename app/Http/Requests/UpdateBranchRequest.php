<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'type' => ['required'],
            'currency_name' => ['required'],
            'currency_symbol' => ['required', 'max:3'],
            'cargo_modes' => ['required'],
            'delivery_types' => ['required'],
            'package_types' => ['required'],
        ];
    }
}
