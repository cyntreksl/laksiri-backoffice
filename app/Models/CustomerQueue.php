<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CustomerQueue extends Model
{
    use HasFactory;

    const DOCUMENT_VERIFICATION_QUEUE = 'DOCUMENT_VERIFICATION_QUEUE';

    const CASHIER_QUEUE = 'CASHIER_QUEUE';

    const EXAMINATION_QUEUE = 'EXAMINATION_QUEUE';

    protected $fillable = [
        'token_id', 'type', 'arrived_at', 'left_at',
    ];

    public function token(): BelongsTo
    {
        return $this->belongsTo(Token::class, 'token_id');
    }

    public function scopeDocumentVerificationQueue(Builder $query): void
    {
        $query->where('type', self::DOCUMENT_VERIFICATION_QUEUE);
    }

    public function scopeCashierQueue(Builder $query): void
    {
        $query->where('type', self::CASHIER_QUEUE);
    }

    public function scopeExaminationQueue(Builder $query): void
    {
        $query->where('type', self::EXAMINATION_QUEUE);
    }
}
