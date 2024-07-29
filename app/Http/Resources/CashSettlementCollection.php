<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashSettlementCollection extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id ?? '-',
            'hbl' => $this->reference ?? '-',
            'hbl_name' => $this->hbl_name ?? '-',
            'address' => $this->address ?? '-',
            'picked_date' => $this->pickup
                ? $this->pickup->pickup_date
                : ($this->created_at ? $this->created_at->format('Y-m-d') : '-'),
//            'weight' => $this->packages
//                ? $this->packages->sum('weight')
//                : '-',
//            'volume' => $this->packages
//                ? $this->packages->sum('volume')
//                : '-',
            'weight' => '3',
            'volume' => '2',
            'grand_total' => $this->grand_total ?? '-',
            'paid_amount' => $this->paid_amount ?? '-',
            'cargo_type' => $this->cargo_type ?? '-',
            'hbl_type' => $this->hbl_type ?? '-',
            'officer' => $this->created_by ?? '-',
            'is_hold' => $this->is_hold ?? '-',
            'status' => $this->hblPayment->status ?? '-',
            'zone' => $this->warehouseZone?->name,
            'actions' => '-',
        ];
    }
}
