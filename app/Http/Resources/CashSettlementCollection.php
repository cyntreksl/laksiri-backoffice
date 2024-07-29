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
        $totalWeight = $this->packages()->sum('weight');
        $totalVolume = $this->packages()->sum('volume');

        return [
            'id' => $this->id ?? '-',
            'hbl' => $this->reference ?? '-',
            'hbl_name' => $this->hbl_name ?? '-',
            'address' => $this->address ?? '-',
            'picked_date' => $this->created_at->format('Y-m-d'),
            'weight' => $totalWeight ?? '-',
            'volume' => $totalVolume ?? '-',
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
