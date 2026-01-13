<?php

namespace App\Models;

use App\Traits\HasQueueLogs;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class CustomerQueue extends Model
{
    use HasFactory;
    use HasQueueLogs;

    const TOKEN_ISSUED = 'TOKEN_ISSUED';

    const RECEPTION_VERIFICATION_QUEUE = 'RECEPTION_VERIFICATION_QUEUE';

    const DOCUMENT_VERIFICATION_QUEUE = 'DOCUMENT_VERIFICATION_QUEUE';

    const CASHIER_QUEUE = 'CASHIER_QUEUE';

    const EXAMINATION_QUEUE = 'EXAMINATION_QUEUE';

    const BOND_AREA_QUEUE = 'BOND_AREA_QUEUE';

    const GATE_PASS_RELEASED = 'GATE_PASS_RELEASED';

    protected $fillable = [
        'token_id', 'type', 'arrived_at', 'left_at',
    ];

    public function token(): BelongsTo
    {
        return $this->belongsTo(Token::class, 'token_id');
    }

    public function scopeReceptionQueue(Builder $query): void
    {
        $query->where('type', self::RECEPTION_VERIFICATION_QUEUE)
              ->whereNull('left_at');
    }

    public function scopeDocumentVerificationQueue(Builder $query): void
    {
        $query->where('type', self::DOCUMENT_VERIFICATION_QUEUE)
              ->whereNull('left_at');
    }

    public function scopeCashierQueue(Builder $query): void
    {
        $query->where('type', self::CASHIER_QUEUE)
              ->whereNull('left_at');
    }

    public function scopeExaminationQueue(Builder $query): void
    {
        $query->where('type', self::EXAMINATION_QUEUE)
              ->whereNull('left_at');
    }

    public function cashierHBLPayment(): HasOne
    {
        return $this->hasOne(CashierHBLPayment::class, 'customer_queue_id');
    }

    public function reception_verification(): HasOne
    {
        return $this->hasOne(ReceptionVerification::class, 'customer_queue_id');
    }

    public function verification(): HasOne
    {
        return $this->hasOne(Verification::class, 'customer_queue_id');
    }

    public function examination(): HasOne
    {
        return $this->hasOne(Examination::class, 'customer_queue_id');
    }
}
