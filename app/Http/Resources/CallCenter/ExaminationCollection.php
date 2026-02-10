<?php

namespace App\Http\Resources\CallCenter;

use App\Models\Scopes\BranchScope;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExaminationCollection extends JsonResource
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
            'released_by' => $this->examination->releasedBy->name,
            'released_at' => $this->examination->created_at->timezone('Asia/Colombo')->format('Y-m-d H:i:s'),
            'hbl' => optional(optional($this->token)->hbl()->withoutGlobalScope(BranchScope::class)->latest()->first())->hbl_number,
            'departed_at' => $this->token->departed_at ? Carbon::parse($this->token->departed_at)->timezone('Asia/Colombo')->format('Y-m-d H:i:s') : null,
            'departed_by' => $this->token->departed_by ? User::find($this->token->departed_by)?->name : null,
            'created_at' => Carbon::parse($this->created_at)->timezone('Asia/Colombo')->format('Y-m-d H:i:s'),
            'total_time' => $this->token->created_at && $this->token->departed_at
                ? Carbon::parse($this->token->created_at)
                    ->diff(Carbon::parse($this->token->departed_at))
                    ->format('%h hours %i minutes %s seconds')
                : 'Not departed',
        ];
    }
}
