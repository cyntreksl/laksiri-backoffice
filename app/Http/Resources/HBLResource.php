<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HBLResource extends JsonResource
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
            'cargo_type' => $this->cargo_type,
            'hbl_type' => $this->hbl_type,
            'hbl' => $this->hbl,
            'hbl_name' => $this->hbl_name,
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
            'warehouse' => $this->warehouse,
            'freight_charge' => $this->freight_charge,
            'bill_charge' => $this->bill_charge,
            'other_charge' => $this->other_charge,
            'discount' => $this->discount,
            'paid_amount' => $this->paid_amount,
            'grand_total' => $this->grand_total,
            'status' => $this->status,
            'is_hold' => $this->is_hold,
            'packages' => $this->packages ? HBLPackageResource::collection($this->packages) : '-',
            'created_by' => $this->user?->name,
            'tokens' => isset($this->tokens[0]) && $this->tokens[0]->created_at->isToday()
                ? 'Token number: '.$this->tokens[0]->id.' '.ucwords(strtolower(str_replace('_', ' ', $this->tokens[0]->customerQueue->type)))
                : '',
            'hbl_number' => $this->hbl_number,
            'cr_number' => $this->cr_number,
            'system_status' => $this->system_status,
            'mhbl' => $this->mhbl && $this->mhbl->hbl_number
                ? $this->mhbl->hbl_number
                : ($this->mhbl && $this->mhbl->reference
                    ? $this->mhbl->reference
                    : null),
            'is_released' => $this->is_released,
            'is_short_loaded' => $this->packages()->whereDoesntHave('containers')->exists(),
            'payment_status' => $this->hblPayment()->latest()->first()->status ?? 'Not Updated',
            'finance_status' => $this->is_finance_release_approved ? 'Approved' : 'Not Approved',
        ];
    }
}
