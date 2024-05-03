<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserCollection extends JsonResource
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
            'username' => $this->username,
            'primary_branch_name' => $this->primaryBranch?->name,
            'created_at' => $this->created_at,
            'status' => $this->status,
            'last_login_at' => $this->last_login_at,
            'last_logout_at' => $this->last_logout_at,
            'secondary_branch_names' => implode(",", $this->branches->pluck('name')->toArray()),
        ];
    }
}
