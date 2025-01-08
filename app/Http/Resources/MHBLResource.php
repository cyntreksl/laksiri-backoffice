<?php

namespace App\Http\Resources;

use App\Actions\Branch\GetBranchById;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MHBLResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'reference' => $this->reference,
            'hbl_number' => $this->hbl_number,
            'cargo_type' => $this->cargo_type,
            'hbl_type' => 'Gift',
            'warehouse' => GetBranchById::run($this->warehouse_id)['name'],
            'shipper_name' => $this->shipper['name'],
            'shipper_email' => $this->shipper['email'],
            'shipper_nic' => $this->shipper['pp_or_nic_no'],
            'shipper_contact' => $this->shipper['mobile_number'],
            'shipper_address' => $this->shipper['address'],
            'shipper_residence_no' => $this->shipper['residency_no'],
            'consignee_name' => $this->consignee['name'],
            'consignee_email' => $this->consignee['email'],
            'consignee_nic' => $this->consignee['pp_or_nic_no'],
            'consignee_contact' => $this->consignee['mobile_number'],
            'consignee_address' => $this->consignee['address'],
            'consignee_residence_no' => $this->consignee['residency_no'],
        ];
    }
}
