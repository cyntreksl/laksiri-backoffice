<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsappContact extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'profile_pic',
        'last_interaction',
    ];

    protected $casts = [
        'last_interaction' => 'datetime',
    ];

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsappMessage::class, 'whatsapp_contact_id');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(WhatsappMessage::class, 'whatsapp_contact_id')
            ->latest('created_at');
    }

    public function unreadMessages(): HasMany
    {
        return $this->messages()
            ->where('message_type', 'received')
            ->where('is_read', false);
    }

    public function getUnreadCountAttribute(): int
    {
        return $this->unreadMessages()->count();
    }

    public function getFormattedPhoneAttribute(): string
    {
        // Format phone number for display
        $phone = $this->phone;
        if (str_starts_with($phone, '+94')) {
            return '+94 '.substr($phone, 3, 2).' '.substr($phone, 5);
        }

        return $phone;
    }

    public function getInitialsAttribute(): string
    {
        if (! $this->name) {
            return substr($this->phone, -2);
        }

        $words = explode(' ', $this->name);
        $initials = '';
        foreach ($words as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }

        return substr($initials, 0, 2);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name ?: 'Contact '.substr($this->phone, -4);
    }

    public function scopeWithUnreadCount($query)
    {
        return $query->withCount(['unreadMessages']);
    }

    public function scopeRecentlyActive($query)
    {
        return $query->whereNotNull('last_interaction')
            ->orderBy('last_interaction', 'desc');
    }

    public function scopeWithLatestMessage($query)
    {
        return $query->with(['latestMessage']);
    }
}
