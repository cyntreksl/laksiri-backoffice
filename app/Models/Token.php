<?php

namespace App\Models;

use App\Enum\TokenStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
        'is_cancelled', 'cancelled_at', 'cancelled_by', 'cancellation_reason', 'status',
    ];

    protected $casts = [
        'is_cancelled' => 'boolean',
        'cancelled_at' => 'datetime',
        'departed_at' => 'datetime',
        'status' => TokenStatus::class,
    ];

    /**
     * Boot the model and register event listeners for automatic status updates.
     */
    protected static function boot()
    {
        parent::boot();

        // Automatically update status when departed_at is set (token completed)
        static::saving(function ($token) {
            // If departed_at is being set and token is not cancelled, mark as COMPLETED
            if ($token->isDirty('departed_at') && $token->departed_at !== null && !$token->is_cancelled) {
                $token->status = TokenStatus::COMPLETED;
            }

            // If token is being cancelled, mark as CANCELLED
            if ($token->isDirty('is_cancelled') && $token->is_cancelled === true) {
                $token->status = TokenStatus::CANCELLED;
            }
        });
    }

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

    public function examination(): HasOne
    {
        return $this->hasOne(Examination::class, 'token_id');
    }

    public function isPaid(): bool
    {
        return false;
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

    public function queueLogs(): HasMany
    {
        return $this->hasMany(QueueLog::class, 'token_id');
    }

    public function cancellation(): HasOne
    {
        return $this->hasOne(TokenCancellation::class);
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }

    public function isCancelled(): bool
    {
        return $this->is_cancelled === true;
    }

    public function isCompleted(): bool
    {
        return $this->departed_at !== null;
    }

    public function canBeCancelled(): bool
    {
        if ($this->isCancelled()) {
            return false;
        }

        if (!$this->isCompleted()) {
            return true;
        }

        // Completed tokens can be cancelled within 3 days
        // diffInDays() returns the number of complete 24-hour periods
        // So a token issued 3 days ago (72 hours) will return 3
        // We want to allow cancellation for 0, 1, 2, and 3 days ago
        $daysSinceIssue = $this->created_at->diffInDays(now());
        return $daysSinceIssue < 4;
    }

    /**
     * Get the current status of the token.
     * This method computes the status dynamically based on token state.
     */
    public function getStatus(): TokenStatus
    {
        if ($this->is_cancelled) {
            return TokenStatus::CANCELLED;
        }

        if ($this->departed_at !== null) {
            return TokenStatus::COMPLETED;
        }

        // Check if token should be marked as DUE
        // Token is DUE if created before today and not completed
        if ($this->created_at->isBefore(now()->startOfDay())) {
            return TokenStatus::DUE;
        }

        return TokenStatus::ONGOING;
    }

    /**
     * Check if token is due (not completed by same day midnight).
     */
    public function isDue(): bool
    {
        return $this->status === TokenStatus::DUE;
    }

    /**
     * Check if token is ongoing.
     */
    public function isOngoing(): bool
    {
        return $this->status === TokenStatus::ONGOING;
    }
}
