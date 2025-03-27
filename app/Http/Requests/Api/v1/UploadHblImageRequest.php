<?php

namespace App\Http\Requests\Api\v1;

use App\Enum\HBLImageType;
use App\Traits\ResponseAPI;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Validator;

class UploadHblImageRequest extends FormRequest
{
    use ResponseAPI;

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
        $rules = [
            'image_type' => 'required|string|in:'.implode(',', array_column(HBLImageType::cases(), 'value')),
            'hbl_id' => 'nullable|exists:hbl,id',
            'hbl_packages_id' => 'nullable|exists:hbl_packages,id',
            'user_id' => 'nullable|exists:users,id',
            'images' => 'required',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ];

        if ($this->image_type === HBLImageType::SHIPPER_NIC->value) {
            $rules['shipper_nic'] = 'nullable|string|max:255';
        }

        if ($this->image_type === HBLImageType::SHIPPER_PASSPORT->value) {
            $rules['shipper_passport'] = 'nullable|string|max:255';
        }

        if ($this->image_type === HBLImageType::PACKAGE->value) {
            $rules['package_images'] = 'required|array';
            $rules['package_images.*'] = 'image|mimes:jpeg,png,jpg,gif|max:2048';
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'images.required' => 'Images are required.',
            'images.*.image' => 'Each file must be an image.',
            'images.*.mimes' => 'Each file must be of type: jpeg, png, jpg, gif.',
            'images.*.max' => 'Each image must not exceed 2MB.',
            'shipper_nic.required_if' => 'The NIC field is required when the image type is shipper.',
            'shipper_passport.required_if' => 'The passport field is required when the image type is shipper.',
            'package_images.required_if' => 'Package images are required when the image type is package.',
        ];
    }

    public function failedValidation(Validator|\Illuminate\Contracts\Validation\Validator $validator): void
    {
        throw new HttpResponseException(
            $this->error('Validation Errors', $validator->errors())
        );
    }
}
