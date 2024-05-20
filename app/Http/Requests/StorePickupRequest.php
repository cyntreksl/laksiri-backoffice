<?php

namespace App\Http\Requests;

use App\Traits\ResponseAPI;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Validator;

class StorePickupRequest extends FormRequest
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
     * @return array<string, ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'cargo_type' => ['required'],
            'name' => ['required'],
            'email' => ['nullable', 'email', 'max:254'],
            'contact_number' => ['required', 'phone:INTERNATIONAL'],
            'address' => ['required'],
            'location' => ['nullable'],
            'zone_id' => ['nullable', 'integer'],
            'notes' => ['required'],
            'pickup_date' => ['required', 'date'],
            'pickup_time_start' => ['nullable', 'date_format:H:i'],
            'pickup_time_end' => ['nullable', 'date_format:H:i'],
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
            'contact_number' => 'mobile number',
        ];
    }

    public function messages()
    {
        return [
            'pickup_time_start.date_format' => 'Invalid date format for entered value.Please enter time like 18:00',
            'pickup_time_end.date_format' => 'Invalid date format for entered value.Please enter time like 13:00',
        ];
    }

    public function failedValidation(Validator|\Illuminate\Contracts\Validation\Validator $validator): void
    {
        if (request()->is('v1/*')) {
            throw new HttpResponseException(
                $this->error('Validation Errors', $validator->errors())
            );
        }

        parent::failedValidation($validator);
    }
}
