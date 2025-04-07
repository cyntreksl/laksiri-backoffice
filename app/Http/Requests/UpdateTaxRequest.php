<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTaxRequest extends FormRequest
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
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                Rule::unique('taxes')
                    ->where(function ($query) {
                        return $query->where('branch_id', session('current_branch_id'));
                    })
                    ->ignore($this->route('tax')),
            ],
            'rate' => ['required', 'numeric'],
            'is_active' => ['required', 'boolean'],
        ];
    }
}
