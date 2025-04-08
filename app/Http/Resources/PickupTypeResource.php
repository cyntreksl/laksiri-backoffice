<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PickupTypeResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'pickup_type_name' => $this->pickup_type_name,
        ];
    }
}
