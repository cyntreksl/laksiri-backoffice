<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContainerPaymentResource extends JsonResource
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
            'container_id' => $this->container->id,
            'containerReference' => $this->container->reference,
            'do_charge' => $this->do_charge,
            'demurrage_charge' => $this->demurrage_charge,
            'assessment_charge' => $this->assessment_charge,
            'slpa_charge' => $this->slpa_charge,
            'refund_charge' => $this->refund_charge,
            'clearance_charge' => $this->clearance_charge,
            'total' => $this->total,
            'is_finance_approved' => $this->is_finance_approved,
            'created_at' => $this->created_at,
        ];
    }
}
