<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UnloadingIssueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $totalWeight = $this->hblPackage->hbl->unloadedPackagesWithoutGlobalScope()->sum('weight');
        $totalVolume = $this->hblPackage->hbl->unloadedPackagesWithoutGlobalScope()->sum('volume');
        $totalQuantity = $this->hblPackage->hbl->unloadedPackagesWithoutGlobalScope()->sum('quantity');

        return [
            'id' => $this->id ?? '-',
            'hbl' => $this->hblPackage->hbl->hbl ?? '-',
            'branch' => 'a',
            'hbl_name' => $this->hblPackage->hbl->hbl_name ?? '-',
            'consignee_name' => $this->hblPackage->hbl->consignee_name ?? '-',
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'weight' => $totalWeight,
            'volume' => $totalVolume,
            'quantity' => $totalQuantity,
            'issue' => $this->issue ?? '-',
            'rtf' => $this->rtf ?? '-',
            'is_damaged' => $this->is_damaged ?? '-',
            'type' => $this->type ?? '-',
            'is_fixed' => $this->is_fixed ?? '-',
        ];
    }
}
