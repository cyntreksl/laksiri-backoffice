<?php

namespace App\Http\Requests;

use App\Models\CustomerQueue;
use App\Models\Token;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class GetPackagesForReturnRequest extends FormRequest
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
            'token' => 'required|string|exists:tokens,token',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $tokenString = $this->route('token');

            if (!$tokenString) {
                return;
            }

            $token = Token::where('token', $tokenString)->first();

            if (!$token) {
                return; // Already handled by exists rule
            }

            // Validation 1: Token must be created today
//            if (!$token->created_at->isToday()) {
//                $validator->errors()->add('token',
//                    'Token must be from today. This token was created on ' .
//                    $token->created_at->format('Y-m-d') . '.'
//                );
//            }

            // Validation 2: Token must be in Examination Queue
            $inExaminationQueue = CustomerQueue::where('token_id', $token->id)
                ->where('type', CustomerQueue::EXAMINATION_QUEUE)
                ->whereNull('left_at')
                ->exists();

            if (!$inExaminationQueue) {
                $validator->errors()->add('token',
                    'Token is not in the Examination Queue. Only tokens currently in the Examination Queue can have packages returned.'
                );
            }
        });
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'token.required' => 'Token number is required',
            'token.exists' => 'Token not found in the system',
        ];
    }
}
