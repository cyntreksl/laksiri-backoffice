<?php

namespace App\Models;

use App\Traits\HasQueueLogs;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReceptionVerification extends Model
{
    use HasFactory;
    use HasQueueLogs;

    protected $fillable = [
        'is_checked', 'verified_by', 'customer_queue_id', 'token_id', 'note',
    ];

    protected $casts = [
        'is_checked' => 'array',
    ];

    public static function reception_verification_documents(): array
    {
        return [
            'Passport',
            'NIC',
            'HBL Receipt',
        ];
    }

    public function token(): BelongsTo
    {
        return $this->belongsTo(Token::class, 'token_id');
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }
}
