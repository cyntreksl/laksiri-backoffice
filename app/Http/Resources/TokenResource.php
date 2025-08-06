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
        return [
            'id' => $this->id,
            'hbl' => $this->when($this->hbl_id, function () {
                return optional($this->hbl)->withoutGlobalScope(BranchScope::class)->latest()->first();
            }),
            'customer' => $this->customer->name,
            'token' => $this->token,
            'reception' => $this->reception->name,
            'package_count' => $this->package_count,
            'finance_status' => $this->when($this->hbl, function () {
                return $this->hbl->is_finance_release_approved ? 'Approved' : 'Not Approved';
            }, 'Not Approved'),
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
