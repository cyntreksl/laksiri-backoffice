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
            'driver_id' => ['required'],
            'picker_note' => ['required', 'string'],
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