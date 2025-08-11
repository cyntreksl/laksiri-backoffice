<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SendWhatsappMessageRequest extends FormRequest
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
        return [
            'recipient' => [
                'required',
                'string',
                'regex:/^[0-9+\-\s()]+$/',
                'min:10',
                'max:15',
            ],
            'message' => [
                'required',
                'string',
                'max:1000',
                'min:1',
            ],
        ];

    }

    /**
     * Get custom error messages for validation rules.
     */
    public function messages(): array
    {
        return [
            'recipient.required' => 'Please select a contact or enter a phone number.',
            'recipient.regex' => 'Please enter a valid phone number.',
            'recipient.min' => 'Phone number must be at least 10 digits.',
            'recipient.max' => 'Phone number cannot exceed 15 digits.',
            'message.required' => 'Message cannot be empty.',
            'message.max' => 'Message cannot exceed 1000 characters.',
            'message.min' => 'Message must contain at least 1 character.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'recipient' => $this->cleanPhoneNumber($this->recipient),
        ]);
    }

    /**
     * Clean phone number by removing unwanted characters
     */
    private function cleanPhoneNumber(?string $phone): ?string
    {
        if (! $phone) {
            return null;
        }

        // Remove spaces, dashes, parentheses but keep + sign
        return preg_replace('/[^\d+]/', '', $phone);
    }
}
