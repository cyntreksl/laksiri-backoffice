<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePickupRequest extends FormRequest
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
            'pickup_date' => ['required', 'date'],
            'pickup_time_start' => ['nullable', 'date_format:H:i'],
            'pickup_time_end' => ['nullable', 'date_format:H:i'],
            'zone_id' => ['nullable', 'integer'],
        ];
    }

    public function messages()
    {
        return [
            'pickup_time_start.date_format' => 'Invalid date format for entered value. Please enter time like 18:00',
            'pickup_time_end.date_format' => 'Invalid date format for entered value. Please enter time like 13:00',
        ];
    }
}
