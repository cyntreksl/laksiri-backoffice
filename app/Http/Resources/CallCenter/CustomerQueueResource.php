<?php

namespace App\Http\Resources\CallCenter;

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
        return [
            'id' => $this->id,
            'token' => $this->token->token,
            'package_count' => $this->token->package_count,
            'reference' => $this->token->reference,
            'created_at' => $this->token->created_at,
            'customer' => $this->token->customer->name,
            'reception' => $this->token->reception->name,
            'is_verified' => $this->token->isVerified(),
        ];
    }
}
