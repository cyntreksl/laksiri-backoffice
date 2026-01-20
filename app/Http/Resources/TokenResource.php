<?php

namespace App\Http\Resources;

use App\Models\Scopes\BranchScope;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TokenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Get the latest queue log for this token
        $latestQueueLog = $this->queueLogs()->latest('created_at')->first();
        $latestQueueType = $latestQueueLog 
            ? ucwords(strtolower(str_replace('_', ' ', $latestQueueLog->queue_type)))
            : null;

        return [
            'id' => $this->id,
            'hbl' => $this->when($this->hbl_id, function () {
                return $this->hbl()
                    ->withoutGlobalScope(BranchScope::class)
                    ->latest()
                    ->first();
            }),
            'customer' => $this->customer->name,
            'token' => $this->token,
            'reception' => $this->reception->name,
            'package_count' => $this->package_count,
            'finance_status' => $this->when($this->hbl, function () {
                return $this->hbl->is_finance_release_approved ? 'Approved' : 'Not Approved';
            }, 'Not Approved'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
            'is_cancelled' => $this->is_cancelled,
            'cancelled_at' => $this->cancelled_at?->format('Y-m-d H:i:s'),
            'can_be_cancelled' => $this->canBeCancelled(),
            'status' => $this->status->value,
            'status_label' => $this->status->getLabel(),
            'status_color' => $this->status->getColor(),
            'latest_queue_type' => $latestQueueType,
        ];
    }
}
