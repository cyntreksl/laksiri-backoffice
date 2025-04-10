<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCurrencyRateRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {

        return [
            'currency_name' => [
                'required',
                'string',
                Rule::unique('currency_rates', 'currency_name')->ignore($this->currency)->whereNull('deleted_at'),
            ],
            'currency_symbol' => [
                'required',
                'string',
                Rule::unique('currency_rates', 'currency_symbol')->ignore($this->currency)->whereNull('deleted_at'),
            ],
            'sl_rate' => ['required', 'numeric', 'min:0'],
        ];
    }
}
