<?php

namespace App\Http\Resources\CallCenter;

use App\Models\Scopes\BranchScope;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageQueueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get the latest HBL once to avoid multiple queries
        $latestHbl = $this->token->hbl()
            ->withoutGlobalScope(BranchScope::class)
            ->latest()
            ->first();

        return [
            'id' => $this->id,
            'token' => $this->token->token,
            'reference' => $this->reference,
            'package_count' => $this->package_count,
            'is_released' => $this->is_released,
            'customer' => $this->token->customer->name,

            'created_at' => $this->token->created_at->format('Y-m-d H:i:s'),

            'hbl' => $latestHbl,
            'hbl_packages' => $latestHbl && $latestHbl->packages ? $latestHbl->packages->map(function ($package) {
                return [
                    'id' => $package->id,
                    'package_type' => $package->package_type,
                    'quantity' => $package->quantity,
                    'length' => $package->length,
                    'width' => $package->width,
                    'height' => $package->height,
                    'weight' => $package->weight,
                    'volume' => $package->volume,
                    'remarks' => $package->remarks,
                    'release_status' => $package->release_status,
                    'bond_storage_number' => $package->bond_storage_number,
                    'released_at' => $package->released_at?->format('Y-m-d H:i:s'),
                ];
            })->values()->toArray() : [],

            'release_logs' => $this->releaseLogs->map(function ($log) {
                return [
                    'type' => $log->type,
                    'packages' => $log->packages,
                    'remarks' => $log->remarks,
                    'timestamp' => $log->created_at->format('Y-m-d H:i:s'),
                    'created_by' => $log->createdBy->name,
                ];
            }),
        ];
    }
}
