<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClearanceContainerRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        $rules = [
            'note' => ['string', 'nullable'],
            'is_reached' => ['required', 'boolean'],
            'is_returned' => ['boolean'],
        ];

        // Only add reached_date validation if is_reached is true
        if ($this->input('is_reached') === true || $this->input('is_reached') === 'true' || $this->input('is_reached') === 1 || $this->input('is_reached') === '1') {
            $rules['reached_date'] = ['required', 'date'];
        } else {
            $rules['reached_date'] = ['nullable', 'date'];
        }

        return $rules;
    }
}
