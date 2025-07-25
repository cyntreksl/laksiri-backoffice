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
            'do_charge_finance_approved' => $this->do_charge_finance_approved,
            'do_charge_requested_at' => $this->do_charge_requested_at ? $formatDate($this->do_charge_requested_at) : null,
            'do_charge_approved_at' => $this->do_charge_approved_at ? $formatDate($this->do_charge_approved_at) : null,
            'do_charge_requested_by' => $this->do_charge_requested_by ? User::find($this->do_charge_requested_by)?->name : null,
            'do_charge_approved_by' => $this->do_charge_approved_by ? User::find($this->do_charge_approved_by)?->name : null,

            'demurrage_charge' => $this->demurrage_charge,
            'demurrage_charge_finance_approved' => $this->demurrage_charge_finance_approved,
            'demurrage_charge_requested_at' => $this->demurrage_charge_requested_at ? $formatDate($this->demurrage_charge_requested_at) : null,
            'demurrage_charge_approved_at' => $this->demurrage_charge_approved_at ? $formatDate($this->demurrage_charge_approved_at) : null,
            'demurrage_charge_requested_by' => $this->demurrage_charge_requested_by ? User::find($this->demurrage_charge_requested_by)?->name : null,
            'demurrage_charge_approved_by' => $this->demurrage_charge_approved_by ? User::find($this->demurrage_charge_approved_by)?->name : null,

            'assessment_charge' => $this->assessment_charge,
            'assessment_charge_finance_approved' => $this->assessment_charge_finance_approved,
            'assessment_charge_requested_at' => $this->assessment_charge_requested_at ? $formatDate($this->assessment_charge_requested_at) : null,
            'assessment_charge_approved_at' => $this->assessment_charge_approved_at ? $formatDate($this->assessment_charge_approved_at) : null,
            'assessment_charge_requested_by' => $this->assessment_charge_requested_by ? User::find($this->assessment_charge_requested_by)?->name : null,
            'assessment_charge_approved_by' => $this->assessment_charge_approved_by ? User::find($this->assessment_charge_approved_by)?->name : null,

            'slpa_charge' => $this->slpa_charge,
            'slpa_charge_finance_approved' => $this->slpa_charge_finance_approved,
            'slpa_charge_requested_at' => $this->slpa_charge_requested_at ? $formatDate($this->slpa_charge_requested_at) : null,
            'slpa_charge_approved_at' => $this->slpa_charge_approved_at ? $formatDate($this->slpa_charge_approved_at) : null,
            'slpa_charge_requested_by' => $this->slpa_charge_requested_by ? User::find($this->slpa_charge_requested_by)?->name : null,
            'slpa_charge_approved_by' => $this->slpa_charge_approved_by ? User::find($this->slpa_charge_approved_by)?->name : null,

            'refund_charge' => $this->refund_charge,
            'refund_charge_finance_approved' => $this->refund_charge_finance_approved,
            'refund_charge_requested_at' => $this->refund_charge_requested_at ? $formatDate($this->refund_charge_requested_at) : null,
            'refund_charge_approved_at' => $this->refund_charge_approved_at ? $formatDate($this->refund_charge_approved_at) : null,
            'refund_charge_requested_by' => $this->refund_charge_requested_by ? User::find($this->refund_charge_requested_by)?->name : null,
            'refund_charge_approved_by' => $this->refund_charge_approved_by ? User::find($this->refund_charge_approved_by)?->name : null,

            'clearance_charge' => $this->clearance_charge,
            'clearance_charge_finance_approved' => $this->clearance_charge_finance_approved,
            'clearance_charge_requested_at' => $this->clearance_charge_requested_at ? $formatDate($this->clearance_charge_requested_at) : null,
            'clearance_charge_approved_at' => $this->clearance_charge_approved_at ? $formatDate($this->clearance_charge_approved_at) : null,
            'clearance_charge_requested_by' => $this->clearance_charge_requested_by ? User::find($this->clearance_charge_requested_by)?->name : null,
            'clearance_charge_approved_by' => $this->clearance_charge_approved_by ? User::find($this->clearance_charge_approved_by)?->name : null,

            'total' => $this->total,
            'is_refund_collected' => $this->is_refund_collected,
            'refund_collected_date' => $this->refund_collected_date ? $formatDate($this->refund_collected_date) : null,
            'refund_collected_by' => $this->refund_collected_by ? User::find($this->refund_collected_by)?->name : null,
            'payment_received_by' => $this->payment_received_by ? User::find($this->payment_received_by)?->name : null,
            'created_by' => $this->created_by ? User::find($this->created_by)?->name : null,
            'created_at' => $formatDate($this->created_at),
            'updated_at' => $formatDate($this->updated_at),
        ];
    }
}
