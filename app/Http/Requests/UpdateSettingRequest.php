<?php

namespace App\Http\Requests;

use App\Models\Setting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateSettingRequest extends FormRequest
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
        $setting = Setting::query()->first();

        return [
            'invoice_header_title' => ['required', 'string'],
            'invoice_header_subtitle' => ['required', 'string'],
            'invoice_header_address' => ['required', 'string'],
            'invoice_header_telephone' => ['required', 'string'],
            'invoice_footer_title' => ['required', 'string'],
            'invoice_footer_text' => ['required', 'string'],
            'logo' => [
                Rule::requiredIf(function () use ($setting) {
                    return (! $setting || ! $setting->logo) && ! $this->logo;
                }),
                Rule::when($this->logo, [
                    'dimensions:max_width=600,max_height=600',
                    'mimes:jpg,jpeg,png',
                    'max:2048',
                ]),
            ],
            'seal' => [
                Rule::requiredIf(function () use ($setting) {
                    return (! $setting || ! $setting->seal) && ! $this->seal;
                }),
                Rule::when($this->seal, [
                    'image',
                    'dimensions:max_width=600,max_height=600',
                    'mimes:jpg,jpeg,png',
                    'max:2048',
                ]),
            ],
            'notification' => [
                'required',
                'array',
                function ($attribute, $value, $fail) {
                    if (! is_array($value) || ! array_filter($value)) {
                        $fail('At least one notification method must be enabled.');
                    }
                },
            ],
        ];
    }
}
