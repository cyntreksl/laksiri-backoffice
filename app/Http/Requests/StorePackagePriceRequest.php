<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePackagePriceRequest extends FormRequest
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
            'destination_branch_id' => ['required', 'integer'],
            'cargo_mode' => ['required'],
            'hbl_type' => ['required'],
            'rule_title' => ['required'],
            'length' => ['required','numeric','min:0'],
            'width' => ['required','numeric','min:0'],
            'height' => ['required','numeric','min:0'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'destination_branch_id.required' => 'The destination branch is required.',
            'cargo_mode.required' => 'The cargo mode is required.',
            'hbl_type.required' => 'The HBL type is required.',
            'rule_title.required' => 'The rule title is required.',
            'length.required' => 'Length is required.',
            'width.required' => 'Width is required.',
            'height.required' => 'Height is required.',
        ];
    }
}
