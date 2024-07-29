<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBranchPriceRequest extends FormRequest
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
            'price_mode' => ['required'],
            'condition' => ['required', 'string'],
            'true_action' => ['required', 'string'],
            'false_action' => ['required', 'string'],
            'bill_price' => ['required', 'numeric'],
            'bill_vat' => ['required', 'numeric'],
            'per_package_charges' => ['required', 'string'],
            'volume_charges' => ['required', 'string'],
            'is_editable' => ['boolean'],
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
            'destination_branch_id' => 'destination branch',
        ];
    }
}
