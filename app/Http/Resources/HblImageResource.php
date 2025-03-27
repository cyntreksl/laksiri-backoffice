<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HblImageResource extends JsonResource
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
            'image_path' => asset('storage/' . $this->image_path),
            'image_type' => $this->image_type,
            'hbl_id' => $this->hbl_id,
            'hbl_packages_id' => $this->hbl_packages_id,
            'user_id' => $this->user_id,
            'shipper_nic' => $this->shipper_nic,
            'shipper_passport' => $this->shipper_passport,
            'package_images' => $this->package_images,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_at' => $this->updated_at->toDateTimeString(),
        ];
    }
}
