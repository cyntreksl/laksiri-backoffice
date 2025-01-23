<?php

namespace App\Http\Requests;

use App\Traits\ResponseAPI;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Validator;

class ReleaseDeliveryRequest extends FormRequest
{
    use ResponseAPI;

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
            'delivery_id' => ['required', 'integer', 'min:1'],
            'hbl_id' => ['required', 'integer', 'min:1'],
            'released_packages' => ['required', 'array', 'min:1'],
        ];
    }

    /**
     * Custom error messages for validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'delivery_id.required' => 'Delivery ID is required.',
            'hbl_id.required' => 'HBL ID is required.',
            'released_packages.required' => 'Please select at least one package.',
            //            'released_packages.array' => 'Released packages must be an array.',
            //            'released_packages.min' => 'Please select at least one package.',
            //            'released_packages.*.distinct' => 'Duplicate entries are not allowed in released packages.',
        ];
    }

    public function failedValidation(Validator|\Illuminate\Contracts\Validation\Validator $validator): void
    {
        throw new HttpResponseException(
            $this->error('Validation Errors', $validator->errors())
        );
    }
}
