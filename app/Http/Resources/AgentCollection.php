<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AgentCollection extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'type' => $this->type,
            'branch_code' => $this->branch_code,
            'currency_name' => $this->currency_name,
            'currency_symbol' => $this->currency_symbol,
            'cargo_modes' => $this->cargo_modes,
            'delivery_types' => $this->delivery_types,
            'package_types' => $this->package_types,

        ];
    }
}
