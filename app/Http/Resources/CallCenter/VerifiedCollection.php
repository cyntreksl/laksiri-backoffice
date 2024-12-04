<?php

namespace App\Http\Resources\CallCenter;

use App\Models\Scopes\BranchScope;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VerifiedCollection extends JsonResource
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
            'token' => $this->token->token,
            'package_count' => $this->token->package_count,
            'reference' => $this->token->reference,
            'customer' => $this->token->customer->name,
            'reception' => $this->token->reception->name,
            'is_checked' => $this->verification->is_checked,
            'verified_by' => $this->verification->verifiedBy->name,
            'note' => $this->verification->note,
            'verified_at' => $this->verification->created_at->format('Y-m-d H:i:s'),
            'hbl' => optional($this->token->hbl()->withoutGlobalScope(BranchScope::class)->latest()->first()),
        ];
    }
}
