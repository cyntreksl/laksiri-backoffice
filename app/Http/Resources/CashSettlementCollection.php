<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Number;

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
            'weight' => Number::format($totalWeight, 2) ?? '-',
            'volume' => Number::format($totalVolume, 2) ?? '-',
            'grand_total' => Number::format($this->grand_total, 2) ?? '-',
            'paid_amount' => Number::format($this->paid_amount, 2) ?? '-',
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
