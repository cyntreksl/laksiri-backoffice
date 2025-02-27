<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class CourierAgentCollection extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'company_name' => $this->company_name,
            'website' => $this->website,
            'contact_number_1' => $this->contact_number_1,
            'contact_number_2' => $this->contact_number_2,
            'email' => $this->email,
            'address' => $this->address,
            'logo' => $this->logo,
            'invoice_header' => $this->invoice_header,
            'invoice_footer' => $this->invoice_footer,


        ];
    }

}
