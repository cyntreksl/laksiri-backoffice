<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReturnPackageRequest extends FormRequest
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
            'token_number' => 'nullable|string', // Made optional for selective returns
            'remarks' => 'nullable|string',
            'selected_packages' => 'sometimes|array|min:1',
            'selected_packages.*.hbl_package_id' => 'required_with:selected_packages|integer|exists:hbl_packages,id',
            'selected_packages.*.package_queue_id' => 'nullable|integer|exists:package_queues,id',
        ];
    }

    /**
     * Custom validation to ensure either token_number or selected_packages is provided
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            if (! $this->token_number && (! $this->selected_packages || empty($this->selected_packages))) {
                $validator->errors()->add('form', 'Either token number or selected packages must be provided.');
            }

            // If selected_packages is provided, ensure at least one package is selected
            if ($this->selected_packages && count($this->selected_packages) === 0) {
                $validator->errors()->add('selected_packages', 'At least one package must be selected for return.');
            }
        });
    }
}
