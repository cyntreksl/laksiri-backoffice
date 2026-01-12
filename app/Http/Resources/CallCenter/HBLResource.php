<?php

namespace App\Http\Resources\CallCenter;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class HBLResource extends JsonResource
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
            'hbl' => $this->hbl,
            'hbl_number' => $this->hbl_number,
            'hbl_name' => $this->hbl_name,
            'email' => $this->email,
            'contact_number' => $this->contact_number,
            'address' => $this->address,
            'consignee_name' => $this->consignee_name,
            'consignee_email' => $this->consignee_email,
            'consignee_contact' => $this->consignee_contact,
            'consignee_address' => $this->consignee_address,
            'cargo_type' => $this->cargo_type,
            'hbl_type' => $this->hbl_type,
            'warehouse' => $this->warehouse,
            'status' => $this->status,
            'system_status' => $this->system_status,
            'is_hold' => $this->is_hold,
            'is_short_loaded' => $this->is_short_loading,
            'finance_status' => $this->finance_status,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Detain related data
            'is_rtf' => $this->latestDetainRecord?->is_rtf ?? false,
            'detain_type' => $this->latestDetainRecord?->detain_type ?? null,
            'latest_detain_record' => $this->latestDetainRecord ? [
                'id' => $this->latestDetainRecord->id,
                'is_rtf' => $this->latestDetainRecord->is_rtf,
                'detain_type' => $this->latestDetainRecord->detain_type,
                'action' => $this->latestDetainRecord->action,
                'detain_reason' => $this->latestDetainRecord->detain_reason,
                'entity_level' => $this->latestDetainRecord->entity_level,
            ] : null,

            // Token related data
            'tokens' => $this->tokens()->orderBy('created_at', 'desc')->first()
                ? [
                    'id' => $this->tokens()->orderBy('created_at', 'desc')->first()->id,
                    'token_number' => $this->tokens()->orderBy('created_at', 'desc')->first()->token,
                    'queue_type' => $this->tokens()->orderBy('created_at', 'desc')->first()->customerQueue()->orderBy('created_at', 'desc')->first()
                        ? ucwords(strtolower(str_replace('_', ' ', $this->tokens()->orderBy('created_at', 'desc')->first()->customerQueue()->orderBy('created_at', 'desc')->first()->type)))
                        : 'N/A',
                    'created_at' => $this->tokens()->orderBy('created_at', 'desc')->first()->created_at->format('Y-m-d H:i:s'),
                    'is_today' => $this->tokens()->orderBy('created_at', 'desc')->first()->created_at->isToday(),
                    'is_cancelled' => $this->tokens()->orderBy('created_at', 'desc')->first()->is_cancelled ?? false,
                    'cancelled_at' => $this->tokens()->orderBy('created_at', 'desc')->first()->cancelled_at?->format('Y-m-d H:i:s'),
                ]
                : null,

            // Call flag related data
            'call_flags_count' => $this->call_flags_count ?? 0,
            'latest_call_flag' => $this->when($this->callFlags && $this->callFlags->isNotEmpty(), function () {
                $latest = $this->callFlags->first();

                return [
                    'caller' => $latest->caller,
                    'date' => $latest->date,
                    'call_outcome' => $latest->call_outcome,
                    'notes' => $latest->notes,
                    'followup_date' => $latest->followup_date,
                    'appointment_date' => $latest->appointment_date,
                    'appointment_notes' => $latest->appointment_notes,
                ];
            }),
            'has_follow_up_due' => $this->hasFollowUpDue(),
            'has_upcoming_appointment' => $this->hasUpcomingAppointment(),
            'follow_up_date' => $this->getNextFollowUpDate(),
            'appointment_date' => $this->getNextAppointmentDate(),
        ];
    }

    /**
     * Check if HBL has follow-up due
     */
    private function hasFollowUpDue(): bool
    {
        if (! $this->callFlags || $this->callFlags->isEmpty()) {
            return false;
        }

        return $this->callFlags
            ->where('followup_date', '<=', now()->toDateString())
            ->where('followup_date', '!=', null)
            ->filter(function ($callFlag) {
                // Check if there's no newer call flag after this follow-up date
                return ! $this->callFlags
                    ->where('date', '>', $callFlag->followup_date)
                    ->isNotEmpty();
            })
            ->isNotEmpty();
    }

    /**
     * Check if HBL has upcoming appointment
     */
    private function hasUpcomingAppointment(): bool
    {
        if (! $this->callFlags || $this->callFlags->isEmpty()) {
            return false;
        }

        return $this->callFlags
            ->where('appointment_date', '>=', now()->toDateString())
            ->where('appointment_date', '!=', null)
            ->isNotEmpty();
    }

    /**
     * Get next follow-up date
     */
    private function getNextFollowUpDate(): ?string
    {
        if (! $this->callFlags || $this->callFlags->isEmpty()) {
            return null;
        }

        $nextFollowUp = $this->callFlags
            ->where('followup_date', '>=', now()->toDateString())
            ->sortBy('followup_date')
            ->first();

        return $nextFollowUp ? $nextFollowUp->followup_date : null;
    }

    /**
     * Get next appointment date
     */
    private function getNextAppointmentDate(): ?string
    {
        if (! $this->callFlags || $this->callFlags->isEmpty()) {
            return null;
        }

        $nextAppointment = $this->callFlags
            ->where('appointment_date', '>=', now()->toDateString())
            ->sortBy('appointment_date')
            ->first();

        return $nextAppointment ? $nextAppointment->appointment_date : null;
    }
}
