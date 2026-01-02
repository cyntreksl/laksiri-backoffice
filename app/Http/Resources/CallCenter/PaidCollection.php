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
            'token' => $this->whenLoaded('token', fn() => $this->token->token),
            'package_count' => $this->whenLoaded('token', fn() => $this->token->package_count),
            'reference' => $this->whenLoaded('token', fn() => $this->token->reference),
            'customer' => $this->whenLoaded('token', function () {
                return $this->token->relationLoaded('customer') ? $this->token->customer->name : null;
            }),
            'reception' => $this->whenLoaded('token', function () {
                return $this->token->relationLoaded('reception') ? $this->token->reception->name : null;
            }),
            'is_paid' => $this->whenLoaded('token', fn() => $this->token->isPaid(), false),
            'verified_by' => $this->whenLoaded('token', function () {
                if ($this->token->relationLoaded('cashierPayment') && $this->token->cashierPayment) {
                    return $this->token->cashierPayment->relationLoaded('verifiedBy') 
                        ? $this->token->cashierPayment->verifiedBy->name 
                        : '';
                }
                return '';
            }, ''),
            'paid_amount' => $this->whenLoaded('token', function () {
                return $this->token->relationLoaded('cashierPayment') ? $this->token->cashierPayment->paid_amount : null;
            }),
            'note' => $this->whenLoaded('token', function () {
                return $this->token->relationLoaded('cashierPayment') ? $this->token->cashierPayment->note : null;
            }),
            'paid_at' => $this->whenLoaded('token', function () {
                if ($this->token->relationLoaded('cashierPayment') && $this->token->cashierPayment) {
                    return $this->token->cashierPayment->created_at?->format('Y-m-d H:i:s');
                }
                return null;
            }),
            'hbl' => null, // HBL number not needed in list view
        ];
    }
}
