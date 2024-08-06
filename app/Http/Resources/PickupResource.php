<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PickupResource extends JsonResource
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
            'reference' => $this->reference,
            'cargo_type' => $this->cargo_type,
            'name' => $this->name,
            'email' => $this->email,
            'contact_number' => $this->contact_number,
            'address' => $this->address,
            'location_name' => $this->location_name,
            'location_longitude' => $this->location_longitude,
            'location_latitude' => $this->location_latitude,
            'zone_id' => $this->zone_id,
            'driver_assigned_at' => $this->driver_assigned_at,
            'pickup_date' => $this->pickup_date,
            'pickup_time_start' => $this->pickup_time_start,
            'pickup_time_end' => $this->pickup_time_end,
            'pickup_order' => $this->pickup_order,
            'driver' => $this->driver?->name ?? '-',
            'pickup_type' => $this->pickup_type ?? '-',
            'pickup_note' => $this->pickup_note ?? '-',
            'packages' => $this->notes ?? '-',
            'exception_note' => $this->latestPickupException && $this->latestPickupException->exceptionType ? $this->latestPickupException->exceptionType->name : '-',
            'hbl' => $this->whenLoaded('hbl', function () {
                return new HBLResource($this->hbl);
            }),
        ];
    }
}
