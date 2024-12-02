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
            'zone' => $this->zone?->name,
            'picker_note' => $this->exceptionType ? $this->exceptionType->name : '-',
            'address' => $this->address,
            'pickup_date' => $this->pickup_date,
            'created_date' => $this->created_at->format('Y-m-d'),
            'driver' => $this->driver?->name,
            'auth' => $this->auth,
            'pickup_id' => $this->pickup_id,
        ];
    }
}
