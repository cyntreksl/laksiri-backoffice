<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class QueueLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'queueable_type', 'queueable_id', 'auth_id', 'customer_id', 'token_id', 'queue_type', 'arrival_at', 'left_at',
    ];

    public function queueable(): MorphTo
    {
        return $this->morphTo();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function token(): BelongsTo
    {
        return $this->belongsTo(Token::class, 'token_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'auth_id');
    }
}
