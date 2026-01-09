<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TokenCancellation extends Model
{
    use HasFactory;

    protected $fillable = [
        'token_id',
        'cancelled_by',
        'cancellation_reason',
        'token_status_at_cancellation',
        'invoice_cancelled',
        'gate_pass_cancelled',
        'hbl_package_status',
        'warnings_shown',
        'post_cancellation_impacts',
    ];

    protected $casts = [
        'invoice_cancelled' => 'boolean',
        'gate_pass_cancelled' => 'boolean',
        'warnings_shown' => 'array',
        'post_cancellation_impacts' => 'array',
        'hbl_package_status' => 'array',
    ];

    /**
     * Boot the model and register immutability protection.
     * Audit logs are read-only once created and cannot be modified or deleted.
     */
    protected static function boot()
    {
        parent::boot();

        // Prevent updates to audit log records
        static::updating(function ($model) {
            // Return false to prevent the update
            return false;
        });

        // Prevent deletion of audit log records
        static::deleting(function ($model) {
            // Return false to prevent the deletion
            return false;
        });
    }

    public function token(): BelongsTo
    {
        return $this->belongsTo(Token::class);
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by');
    }
}
