<?php

namespace App\Http\Resources\CallCenter;

use App\Models\Scopes\BranchScope;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PackageCollection extends JsonResource
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
            'package_count' => $this->token->package_count,
            'reference' => $this->token->reference,
            'released_at' => $this->released_at,
            'released_packages' => $this->released_packages,
            'note' => $this->note,
            'released_by' => $this->releasedBy->name,
            'token' => $this->token->token,
            'hbl' => optional($this->token->hbl()->withoutGlobalScope(BranchScope::class)->latest()->first()),
        ];
    }
}
