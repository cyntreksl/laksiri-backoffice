<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Carbon;

class ContainerResource extends JsonResource
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
            'cargo_type' => $this->cargo_type,
            'branch' => $this->branch->name,
            'container_type' => $this->container_type,
            'reference' => $this->reference,
            'bl_number' => $this->bl_number,
            'awb_number' => $this->awb_number,
            'container_number' => $this->container_number,
            'seal_number' => $this->seal_number,
            'maximum_volume' => $this->maximum_volume,
            'minimum_volume' => $this->minimum_volume,
            'maximum_weight' => $this->maximum_weight,
            'minimum_weight' => $this->minimum_weight,
            'maximum_volumetric_weight' => $this->maximum_volumetric_weight,
            'minimum_volumetric_weight' => $this->minimum_volumetric_weight,
            'estimated_time_of_departure' => $this->estimated_time_of_departure,
            'estimated_time_of_arrival' => $this->estimated_time_of_arrival,
            'vessel_name' => $this->vessel_name,
            'voyage_number' => $this->voyage_number,
            'shipping_line' => $this->shipping_line,
            'port_of_loading' => $this->port_of_loading,
            'port_of_discharge' => $this->port_of_discharge,
            'flight_number' => $this->flight_number,
            'airline_name' => $this->airline_name,
            'airport_of_departure' => $this->airport_of_departure,
            'airport_of_arrival' => $this->airport_of_arrival,
            'cargo_class' => $this->cargo_class,
            'status' => $this->status,
            'loading_started_at' => $this->loading_started_at,
            'loading_ended_at' => $this->loading_ended_at,
            'unloading_started_at' => $this->unloading_started_at,
            'unloading_ended_at' => $this->unloading_ended_at,
            'loading_started_by' => $this->loading_started_by,
            'loading_ended_by' => $this->loading_ended_by,
            'unloading_started_by' => $this->unloading_started_by,
            'unloading_ended_by' => $this->unloading_ended_by,
            'note' => $this->note,
            'is_reached' => $this->is_reached ? 'REACHED' : 'PENDING',
            'arrived_at_primary_warehouse' => $this->arrived_at_primary_warehouse ? Carbon::parse($this->arrived_at_primary_warehouse)->toDateTimeString() : null,
            'departed_at_primary_warehouse' => $this->departed_at_primary_warehouse ? Carbon::parse($this->arrived_at_primary_warehouse)->toDateTimeString() : null,
        ];
    }
}
