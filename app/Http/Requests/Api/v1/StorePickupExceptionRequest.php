<?php

namespace App\Http\Requests\Api\v1;

use App\Traits\ResponseAPI;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Validator;

class StorePickupExceptionRequest extends FormRequest
{
    use ResponseAPI;

    public function rules(): array
    {
        return [
            'exception_name_id' => ['required'],
            'remarks' => 'nullable|string|max:255',
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
            'driver_id' => 'driver',
            'exception_name_id' => 'exception type',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function failedValidation(Validator|\Illuminate\Contracts\Validation\Validator $validator): void
    {
        throw new HttpResponseException(
            $this->error('Validation Errors', $validator->errors())
        );
    }
}
