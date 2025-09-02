<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsappMessage extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'whatsapp_contact_id',
        'message',
        'message_type',
        'message_id',
        'sent_at',
        'received_at',
        'is_read',
        'delivery_status',
        'metadata',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
        'received_at' => 'datetime',
        'is_read' => 'boolean',
        'metadata' => 'array',
    ];

    public function contact(): BelongsTo
    {
        return $this->belongsTo(WhatsappContact::class, 'whatsapp_contact_id');
    }

    public function scopeSent($query)
    {
        return $query->where('message_type', 'sent');
    }

    public function scopeReceived($query)
    {
        return $query->where('message_type', 'received');
    }

    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    public function getTimestampAttribute()
    {
        return $this->sent_at ?? $this->received_at ?? $this->created_at;
    }

    public function getIsOutgoingAttribute(): bool
    {
        return $this->message_type === 'sent';
    }

    public function getIsIncomingAttribute(): bool
    {
        return $this->message_type === 'received';
    }

    public function getFormattedTimeAttribute(): string
    {
        $timestamp = $this->timestamp;

        if ($timestamp->isToday()) {
            return $timestamp->format('H:i');
        } elseif ($timestamp->isYesterday()) {
            return 'Yesterday '.$timestamp->format('H:i');
        } else {
            return $timestamp->format('M j, H:i');
        }
    }
}
