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
            'hbl' => $this->hbl_id ? $this->hbl->withoutGlobalScope(BranchScope::class)->latest()->first() : null,
            'customer' => $this->customer->name,
            'token' => $this->token,
            'reception' => $this->reception->name,
            'package_count' => $this->package_count,
            'created_at' => $this->created_at->format('Y-m-d H:i:s'),
        ];
    }
}
