<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BondedWarehouseCollection extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        $totalWeight = $this->unloadedPackagesWithoutGlobalScope()->sum('weight');
        $totalVolume = $this->unloadedPackagesWithoutGlobalScope()->sum('volume');
        $totalQuantity = $this->unloadedPackagesWithoutGlobalScope()->sum('quantity');

        return [
            'id' => $this->id ?? '-',
            'hbl' => $this->hbl_number ?? '-',
            'hbl_name' => $this->hbl_name ?? '-',
            'consignee_name' => $this->consignee_name ?? '-',
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'weight' => $totalWeight,
            'volume' => $totalVolume,
            'quantity' => $totalQuantity,
            'hbl_type' => $this->hbl_type ?? '-',
            'is_short_load' => $this->is_short_loading,
        ];
    }
}
