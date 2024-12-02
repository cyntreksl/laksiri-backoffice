<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUnloadingIssue extends FormRequest
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
            'hbl_package_id' => 'required',
            'issue' => 'required|string|min:3|max:255',
            'rtf' => 'nullable|boolean',
            'is_damaged' => 'nullable|boolean',
            'type' => 'required|string|min:3|max:255',
        ];
    }
}
