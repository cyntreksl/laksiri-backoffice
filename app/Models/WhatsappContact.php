<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class WhatsappContact extends Model
{
    use HasFactory , SoftDeletes;

    protected $fillable = [
        'name',
        'phone',
        'profile_pic',
        'last_interaction',
    ];

    protected $casts = [
        'last_interaction' => 'datetime',
    ];

    /**
     * Create or update contact when receiving a message
     */
    public static function createOrUpdateFromMessage($phone, $name = null, $profilePic = null)
    {
        return self::updateOrCreate(
            ['phone' => $phone],
            [
                'name' => $name ?? self::where('phone', $phone)->value('name'),
                'profile_pic' => $profilePic ?? self::where('phone', $phone)->value('profile_pic'),
                'last_interaction' => now(),
            ]
        );
    }

    /**
     * Get formatted phone number
     */
    public function getFormattedPhoneAttribute()
    {
        // Format phone number for display
        return preg_replace('/(\d{3})(\d{3})(\d{4})/', '($1) $2-$3', $this->phone);
    }

    /**
     * Get time since last interaction
     */
    public function getLastInteractionHumanAttribute()
    {
        return $this->last_interaction ? $this->last_interaction->diffForHumans() : 'Never';
    }

    /**
     * Scope for recent contacts
     */
    public function scopeRecent($query, $days = 30)
    {
        return $query->where('last_interaction', '>=', Carbon::now()->subDays($days));
    }

    /**
     * Scope for ordering by last interaction
     */
    public function scopeOrderByLastInteraction($query, $direction = 'desc')
    {
        return $query->orderBy('last_interaction', $direction);
    }
}
