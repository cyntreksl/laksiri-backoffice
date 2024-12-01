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
        return [
            'id' => $this->id,
            'token' => $this->token()->exists() ?? $this->token->token,
            'reference' => $this->reference,
            'package_count' => $this->package_count,
            'is_released' => $this->is_released,
            'created_at' => $this->token()->exists() ?? $this->token->created_at->format('Y-m-d H:i:s'),
            'hbl' => $this->token->hbl()->withoutGlobalScope(BranchScope::class)->latest()->first(),
        ];
    }
}
