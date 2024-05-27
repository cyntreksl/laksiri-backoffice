<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBranchPriceRequest extends FormRequest
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
            'destination_branch_id' => ['required', 'integer'],
            'cargo_mode' => ['required'],
            'price_mode' => ['required'],
            'condition' => ['required', 'string'],
            'true_action' => ['required', 'string'],
            'false_action' => ['required', 'string'],
            'is_editable' => ['boolean'],
        ];
    }
}
