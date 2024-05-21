<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PickupExceptionResource extends JsonResource
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
            'reference' => $this?->reference,
            'name' => $this?->name,
            'zone' => $this->zone_id,
            'picker_note' => $this->picker_note,
            'address' => $this->address,
            'pickup_date' => $this->pickup_date,
            'created_date' => $this->created_at,
            'driver' => $this->driver_id,
            'auth' => $this->auth,
        ];
    }
}
