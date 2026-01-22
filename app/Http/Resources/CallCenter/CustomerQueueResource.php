<?php

namespace App\Http\Resources\CallCenter;

use App\Models\PackageQueue;
use App\Models\Scopes\BranchScope;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerQueueResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $packageQueue = PackageQueue::where('token_id', $this->token_id)->first();
        
        $is_released_from_boned_area = $packageQueue?->is_released ?? false;
        $released_package_count = $packageQueue?->released_package_count ?? 0;
        $held_package_count = $packageQueue?->held_package_count ?? 0;
        $total_package_count = $this->token->package_count;
        
        // Determine release status
        $release_status = 'not_released';
        if ($released_package_count > 0 && $held_package_count > 0) {
            $release_status = 'partially_released';
        } elseif ($released_package_count > 0 && $held_package_count === 0) {
            $release_status = 'fully_released';
        }

        $hbl = $this->token->hbl()->withoutGlobalScope(BranchScope::class)->first();

        return [
            'id' => $this->id,
            'token' => $this->token->token,
            'package_count' => $total_package_count,
            'reference' => $this->token->reference,
            'hbl_number' => $hbl?->hbl_number,
            'created_at' => $this->token->created_at->format('Y-m-d H:i:s'),
            'customer' => $this->token->customer->name,
            'reception' => $this->token->reception->name,
            'is_verified' => $this->token->isVerified(),
            'is_paid' => $this->token->isPaid(),
            'is_released_from_boned_area' => $is_released_from_boned_area,
            'release_status' => $release_status,
            'released_package_count' => $released_package_count,
            'held_package_count' => $held_package_count,
            'is_force_released' => $this->examination()->exists(),
            'hbl' => $hbl,
            'is_reception_verified' => $this->token->isReceptionVerified(),
            'hbl_packages' => optional($hbl)->packages,
        ];
    }
}
