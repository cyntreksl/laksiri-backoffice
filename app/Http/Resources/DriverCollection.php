<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @see \App\Models\User */
class DriverCollection extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'username' => $this->username,
            'primary_branch_name' => $this->primaryBranch?->name,
            'created_at' => Carbon::parse($this->created_at)->toDateTimeString(),
            'status' => $this->status,
        ];
    }
}
