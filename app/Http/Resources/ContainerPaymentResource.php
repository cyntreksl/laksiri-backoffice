<?php

namespace App\Http\Resources;

use App\Models\User;
use Carbon\Carbon;
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
        $formatDate = fn ($date) => optional(Carbon::parse($date))->toDateTimeString();

        return [
            'id' => $this->id,
            'container_id' => optional($this->container)->id,
            'containerReference' => optional($this->container)->reference,
            'do_charge' => $this->do_charge,
            'demurrage_charge' => $this->demurrage_charge,
            'assessment_charge' => $this->assessment_charge,
            'slpa_charge' => $this->slpa_charge,
            'refund_charge' => $this->refund_charge,
            'clearance_charge' => $this->clearance_charge,
            'total' => $this->total,
            'is_finance_approved' => $this->is_finance_approved,
            'is_refund_collected' => $this->is_refund_collected,
            'finance_approved_date' => $this->finance_approved_date ? $formatDate($this->finance_approved_date) : null,
            'refund_collected_date' => $this->refund_collected_date ? $formatDate($this->refund_collected_date) : null,
            'refund_collected_by' => $this->refund_collected_by ? User::find($this->refund_collected_by)?->name : null,
            'payment_received_by' => $this->payment_received_by ? User::find($this->payment_received_by)?->name : null,
            'finance_approved_by' => $this->finance_approved_by ? User::find($this->finance_approved_by)?->name : null,
            'created_by' => $this->created_by ? User::find($this->created_by)?->name : null,
            'created_at' => $formatDate($this->created_at),
            'updated_at' => $formatDate($this->updated_at),
        ];
    }
}
