<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CurrencyRateResourse extends JsonResource
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
            'currency_name' => $this->currency_name,
            'currency_symbol' => $this->currency_symbol,
            'sl_rate' => $this->sl_rate,
            'created_at' => $this->created_at->toDateTimeString(),
            'updated_by' => $this->creator?->name,
        ];
    }
}
