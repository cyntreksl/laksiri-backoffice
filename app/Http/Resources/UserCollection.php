<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

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
            'role' => Str::ucfirst($this->getRoleName()),
            'role_id' => $this->getRoleId(),
            'primary_branch_name' => $this->primaryBranch?->name,
            'created_at' => Carbon::parse($this->created_at)->toDateTimeString(),
            'status' => $this->status,
            'last_login_at' => $this->last_login_at,
            'last_logout_at' => $this->last_logout_at,
            'secondary_branch_names' => implode(',', $this->branches->pluck('name')->toArray()),
        ];
    }

    /**
     * Get the user's role name.
     */
    protected function getRoleName(): string
    {
        return $this->roles->pluck('name')->first() ?? '';
    }

    /**
     * Get the user's role ID.
     */
    protected function getRoleId(): ?int
    {
        return $this->roles->pluck('id')->first();
    }
}
