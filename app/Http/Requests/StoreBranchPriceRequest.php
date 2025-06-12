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
            'priceRules' => ['required', 'array', 'min:1'],
            'priceRules.*.condition' => ['required', 'string'],
            'priceRules.*.true_action' => ['required', 'string'],
            'priceRules.*.bill_price' => ['required', 'numeric', 'min:0'],
            'priceRules.*.bill_vat' => ['required', 'numeric', 'min:0'],
            'priceRules.*.volume_charges' => ['required', 'numeric', 'min:0'],
            'priceRules.*.per_package_charges' => ['required', 'numeric', 'min:0'],
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
            'priceRules.*.condition' => 'condition',
            'priceRules.*.true_action' => 'true action',
            'priceRules.*.bill_price' => 'bill price',
            'priceRules.*.bill_vat' => 'bill vat',
            'priceRules.*.volume_charges' => 'volume charges',
            'priceRules.*.per_package_charges' => 'per package charges',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $priceRules = $this->input('priceRules', []);
            $conditions = array_column($priceRules, 'condition');

            // Check for duplicate conditions in the request
            if (count($conditions) !== count(array_unique($conditions))) {
                $validator->errors()->add('priceRules', 'Duplicate conditions are not allowed.');
            }
        });
    }
}
