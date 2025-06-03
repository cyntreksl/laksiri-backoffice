<?php

namespace App\Http\Requests;

use App\Models\Currency;
use Illuminate\Foundation\Http\FormRequest;

class StoreCurrencyRateRequest extends FormRequest
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
            ],
            'currency_symbol' => [
                'required',
                'string',
            ],
            'sl_rate' => [
                'required',
                'numeric',
                'min:0',
                function ($attribute, $value, $fail) {
                    $currencyName = $this->input('currency_name');
                    $today = now()->toDateString();

                    $exists = Currency::where('currency_name', $currencyName)
                        ->whereDate('created_at', $today)
                        ->where('sl_rate', $value)
                        ->exists();

                    if ($exists) {
                        $fail('The same SL rate value already exists today for this currency.');
                    }
                },
            ],
        ];
    }
}
