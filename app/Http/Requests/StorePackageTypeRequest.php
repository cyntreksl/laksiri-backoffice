<?php

namespace App\Http\Requests;

use App\Actions\User\GetUserCurrentBranchID;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePackageTypeRequest extends FormRequest
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
        $branchId = GetUserCurrentBranchID::run();

        return [
            'name' => [
                'required',
                'string',
                'max:250',
                Rule::unique('package_types')->whereNull('deleted_at')->where(function ($query) use ($branchId) {
                    return $query->where('branch_id', $branchId);
                }),
            ],
            'description' => [
                'nullable',
                'string',
            ],
        ];
    }
}
