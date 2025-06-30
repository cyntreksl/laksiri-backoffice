<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreCallFlagRequest extends FormRequest
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
            'caller' => ['required', 'string', 'max:200'],
            'date' => ['required', 'date', 'before_or_equal:today'],
            'notes' => ['nullable', 'string'],
            'followup_date' => ['nullable', 'date', 'after:today'],
            'call_outcome' => ['required', 'string', 'in:contacted,no_answer,busy,appointment_scheduled,not_available'],
            'appointment_date' => ['nullable', 'date', 'after:today', 'required_if:call_outcome,appointment_scheduled'],
            'appointment_notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'caller.required' => 'The receiver name is required.',
            'date.before_or_equal' => 'The call date cannot be in the future.',
            'followup_date.after' => 'The follow-up date must be in the future.',
            'call_outcome.required' => 'Please select a call outcome.',
            'call_outcome.in' => 'Please select a valid call outcome.',
            'appointment_date.required_if' => 'Appointment date is required when appointment is scheduled.',
            'appointment_date.after' => 'The appointment date must be in the future.',
        ];
    }
}
