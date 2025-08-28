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
        $is_released_from_boned_area = PackageQueue::where('token_id', $this->token_id)->value('is_released');

        return [
            'id' => $this->id,
            'token' => $this->token->token,
            'package_count' => $this->token->package_count,
            'reference' => $this->token->reference,
            'created_at' => $this->token->created_at->format('Y-m-d H:i:s'),
            'customer' => $this->token->customer->name,
            'reception' => $this->token->reception->name,
            'is_verified' => $this->token->isVerified(),
            'is_paid' => $this->token->isPaid(),
            'is_released_from_boned_area' => $is_released_from_boned_area,
            'is_force_released' => $this->examination()->exists(),
            'hbl' => optional(optional($this->token)->hbl()->withoutGlobalScope(BranchScope::class)->latest()->first()),
            'is_reception_verified' => $this->token->isReceptionVerified(),
            'hbl_packages' => optional(
                $this->token->hbl()->withoutGlobalScope(BranchScope::class)->latest()->first()
            )->packages,
        ];
    }
}
