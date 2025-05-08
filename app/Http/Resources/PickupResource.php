<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Str;

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
            'additional_mobile_number' => $this->additional_mobile_number,
            'whatsapp_number' => $this->whatsapp_number,
            'address' => $this->address,
            'location_name' => $this->location_name,
            'location_longitude' => $this->location_longitude,
            'location_latitude' => $this->location_latitude,
            'zone' => $this->zone ? $this->zone->name : '-',
            'driver_assigned_at' => $this->driver_assigned_at,
            'pickup_date' => $this->pickup_date ?: '-',
            'pickup_time_start' => $this->pickup_time_start ?: '-',
            'pickup_time_end' => $this->pickup_time_end ?: '-',
            'pickup_order' => $this->pickup_order,
            'driver' => $this->driver?->name ?: '-',
            'pickup_type' => $this->pickup_type ?: '-',
            'pickup_note' => $this->pickup_note ?: '-',
            'packages' => $this->hbl && $this->hbl->packages->isNotEmpty() ? HBLPackageResource::collection($this->hbl->packages) : $this->package_types,
            'exception_note' => $this->latestPickupException && $this->latestPickupException->exceptionType ? $this->latestPickupException->exceptionType->name : '-',
            'hbl' => $this->whenLoaded('hbl', function () {
                return new HBLResource($this->hbl);
            }),
            'retry_attempts' => $this->retry_attempts,
            'created_by' => $this->createdBy->name ?? '-',
            'hbl_number' => $this->hbl_number,
            'cr_number' => $this->cr_number,
            'status' => $this->status,
            'package_types' => Str::title($this->package_types),
        ];
    }
}
