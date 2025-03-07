<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @see \App\Models\User */
class CourierCollection extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'select_couriers' =>true,
            'id' => $this->courier_number,
            'courier_number' => $this->courier_number,
            'name' => $this->name,
            'email' => $this->email,
            'contact_number' => $this->contact_number,
            'nic' => $this->nic,
            'iq_number' => $this->iq_number,
            'address' => $this->address,
            'consignee_name' => $this->consignee_name,
            'consignee_nic' => $this->consignee_nic,
            'consignee_contact' => $this->consignee_contact,
            'consignee_address' => $this->consignee_address,
            'consignee_note' => $this->consignee_note,
            'courier_agent' => $this->courierAgent['company_name'],
            'cargo_type' => $this->cargo_type,
            'hbl_type' => $this->hbl_type,
            'status' => $this->status,
            'created_at' => Carbon::parse($this->created_at)->toDateTimeString(),
        ];
    }
}
