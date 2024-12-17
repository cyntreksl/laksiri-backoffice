<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreShipperRequest extends FormRequest
{
    public function authorize()
    {
        return true; // Adjust this based on your authorization logic
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'mobile_number' => 'required|string|max:20',
            'pp_or_nic_no' => 'required|string|max:255',
            'residency_no' => 'required|string|max:255',
            'type' => 'required|in:shipper,other_type',
            'description' => 'string|max:500',

        ];
    }
}
