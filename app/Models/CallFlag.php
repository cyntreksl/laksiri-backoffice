<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class CallFlag extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'hbl_id',
        'caller',
        'date',
        'notes',
        'followup_date',
        'call_outcome',
        'appointment_date',
        'appointment_notes',
        'created_by',
    ];

    public function hbl(): BelongsTo
    {
        return $this->belongsTo(HBL::class, 'hbl_id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

    public function causer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function isFollowUpDue(): bool
    {
        return $this->followup_date &&
               $this->followup_date <= now()->toDateString() &&
               !$this->hasCompletedFollowUp();
    }

    public function hasCompletedFollowUp(): bool
    {
        return $this->hbl->callFlags()
            ->where('date', '>', $this->followup_date)
            ->exists();
    }

    public function hasAppointment(): bool
    {
        return !is_null($this->appointment_date);
    }

    public function isAppointmentUpcoming(): bool
    {
        return $this->hasAppointment() &&
               $this->appointment_date >= now()->toDateString();
    }
}
