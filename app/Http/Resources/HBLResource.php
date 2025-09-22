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
        $totalPackages = $this->packages()->count();
        $loadedPackages = $this->packages()->where('is_loaded', 1)->count();

        $isShortLoad = $loadedPackages > 0 && $loadedPackages < $totalPackages;

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
            'tokens' => $this->tokens()->orderBy('created_at', 'desc')->first()
                ? [
                    'token_number' => $this->tokens()->orderBy('created_at', 'desc')->first()->token,
                    'queue_type' => ucwords(strtolower(str_replace('_', ' ', $this->tokens()->orderBy('created_at', 'desc')->first()->customerQueue()->orderBy('created_at', 'desc')->first()->type))),
                    'created_at' => $this->tokens()->orderBy('created_at', 'desc')->first()->created_at->format('Y-m-d H:i:s'),
                    'is_today' => $this->tokens()->orderBy('created_at', 'desc')->first()->created_at->isToday(),
                ]
                : null,
            'hbl_number' => $this->hbl_number,
            'cr_number' => $this->cr_number,
            'system_status' => $this->system_status,
            'mhbl' => $this->mhbl && $this->mhbl->hbl_number
                ? $this->mhbl->hbl_number
                : ($this->mhbl && $this->mhbl->reference
                    ? $this->mhbl->reference
                    : null),
            'is_released' => $this->is_released,
            'is_short_loaded' => $isShortLoad,
            'payment_status' => $this->hblPayment()->latest()->first()->status ?? 'Not Updated',
            'finance_status' => $this->is_finance_release_approved ? 'Approved' : 'Not Approved',
            'currency_rate' => $this->currency_rate ?? 1.0,
            'is_rtf' => $this->latestDetainRecord?->is_rtf ?? false,
            'detain_type' => $this->latestDetainRecord?->detain_type ?? null,
            'is_destination_charges_paid' => $this->is_destination_charges_paid,
            'is_departure_charges_paid' => $this->is_departure_charges_paid,
            'is_third_party' => $this->is_third_party,
        ];
    }
}
