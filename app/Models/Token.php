<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Token extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'hbl_id', 'customer_id', 'receptionist_id', 'reference', 'package_count', 'token', 'departed_by', 'departed_at',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

    public function customerQueue(): HasOne
    {
        return $this->hasOne(CustomerQueue::class, 'token_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function reception(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receptionist_id');
    }

    public function reception_verification(): HasOne
    {
        return $this->hasOne(ReceptionVerification::class, 'token_id');
    }

    public function verification(): HasOne
    {
        return $this->hasOne(Verification::class, 'token_id');
    }

    public function isReceptionVerified(): bool
    {
        return $this->reception_verification()->exists();
    }

    public function isVerified(): bool
    {
        return $this->verification()->exists();
    }

    public function cashierPayment(): HasOne
    {
        return $this->hasOne(CashierHBLPayment::class, 'token_id');
    }

    public function isPaid(): bool
    {
        $hbl = HBL::withoutGlobalScopes()
            ->where('reference', $this->reference)->firstOrFail();

        $payment = $hbl->hblPayment()->withoutGlobalScopes()->latest()->first();

        if ($payment) {
            if ($hbl->paid_amount >= $payment->grand_total) {
                return true;
            } else {
                return false;
            }
        }

        if ($this->cashierPayment()->exists()) {
            return true;
        }

        return false;
    }

    public function hbl(): BelongsTo
    {
        return $this->belongsTo(HBL::class, 'hbl_id', 'id');
    }
}
