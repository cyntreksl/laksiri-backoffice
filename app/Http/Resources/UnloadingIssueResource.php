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
        return [
            'id' => $this->id ?? '-',
            'hbl' => $this->hblPackage->hbl->hbl_number ?? '-',
            'branch' => $this->hblPackage->hbl->branch->name ?? '-',
            'hbl_name' => $this->hblPackage->hbl->hbl_name ?? '-',
            'consignee_name' => $this->hblPackage->hbl->consignee_name ?? '-',
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'weight' => $this->hblPackage?->weight,
            'volume' => $this->hblPackage?->volume,
            'quantity' => $this->hblPackage?->quantity,
            'issue' => $this->issue ?? '-',
            'rtf' => $this->rtf ?? '-',
            'is_damaged' => $this->is_damaged ? 'Yes' : 'No',
            'type' => $this->type ?? '-',
            'is_fixed' => $this->is_fixed ?? '-',
            'remarks' => $this->remarks ?? '-',
            'note' => $this->note ?? '-',
            'has_photos' => $this->files()->count() > 0,
            'photos_count' => $this->files()->count(),
        ];
    }
}
