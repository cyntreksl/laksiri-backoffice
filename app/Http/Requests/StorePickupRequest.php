<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StorePickupRequest extends FormRequest
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
            'cargo_type' => ['required'],
            'name' => ['required'],
            'email' => ['nullable', 'email', 'max:254'],
            'contact_number' => ['required'],
            'address' => ['required'],
            'location' => ['nullable'],
            'zone_id' => ['nullable', 'integer'],
            'notes' => ['required'],
            'pickup_date' => ['required', 'date'],
            'pickup_time_start' => ['nullable', 'date_format:H:i a'],
            'pickup_time_end' => ['nullable', 'date_format:H:i a'],
        ];
    }

    public function messages()
    {
        return [
            'pickup_time_start.date_format' => "Invalid date format for entered value.Please enter time like 10:00 am",
            'pickup_time_end.date_format' => "Invalid date format for entered value.Please enter time like 11:00 am",
        ];
    }

}
