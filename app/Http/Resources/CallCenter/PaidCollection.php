<?php

namespace App\Http\Resources\CallCenter;

use App\Models\Scopes\BranchScope;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaidCollection extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @return array<int|string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'token' => optional($this->token)->token,
            'package_count' => optional($this->token)->package_count,
            'reference' => optional($this->token)->reference,
            'customer' => optional(optional($this->token)->customer)->name,
            'reception' => optional(optional($this->token)->reception)->name,
            'is_paid' => optional($this->token)?->isPaid() ?? false,
            'verified_by' => optional($this->cashierHBLPayment?->verifiedBy)->name ?? '',
            'paid_amount' => optional($this->cashierHBLPayment)->paid_amount,
            'note' => optional($this->cashierHBLPayment)->note,
            'paid_at' => optional($this->cashierHBLPayment?->created_at)?->format('Y-m-d H:i:s'),
            'hbl' => optional(
                optional($this->token)?->hbl()
                    ->withoutGlobalScope(BranchScope::class)
                    ->latest()
                    ->first()
            )?->hbl_number,
        ];
    }
}
