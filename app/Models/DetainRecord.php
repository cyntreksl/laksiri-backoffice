<?php

namespace App\Models;

use App\Actions\User\GetUserCurrentBranchID;
use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy(BranchScope::class)]
class DetainRecord extends Model
{
    use LogsActivity;

    protected $fillable = [
        'is_rtf',
        'detain_type',
        'action',
        'detain_reason',
        'lift_reason',
        'remarks',
        'rtf_by',
        'lifted_by',
        'lifted_at',
        'note',
        'entity_level'
    ];

    protected $table = 'detain_records';

    protected $casts = [
        'is_rtf' => 'boolean',
        'lifted_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($record) {
            if (empty($record->branch_id)) {
                $record->branch_id = GetUserCurrentBranchID::run();
            }
            
            // Auto-set entity_level based on rtfable_type if not set
            if (empty($record->entity_level) && !empty($record->rtfable_type)) {
                $record->entity_level = match($record->rtfable_type) {
                    'App\Models\Container' => 'shipment',
                    'App\Models\HBL' => 'hbl',
                    'App\Models\HBLPackage' => 'package',
                    default => null,
                };
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logOnlyDirty()
            ->setDescriptionForEvent(fn(string $eventName) => "Detain record {$eventName}");
    }

    public function rtfable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rtf_by');
    }

    public function detainedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rtf_by');
    }

    public function liftedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'lifted_by');
    }

    public function branch(): HasOne
    {
        return $this->hasOne(Branch::class, 'id', 'branch_id');
    }

    /**
     * Scope to get only active detains
     */
    public function scopeActive($query)
    {
        return $query->where('is_rtf', true)->where('action', 'detain');
    }

    /**
     * Scope to get only lifted detains
     */
    public function scopeLifted($query)
    {
        return $query->where('action', 'lift');
    }

    /**
     * Check if this is an active detain
     */
    public function isActive(): bool
    {
        return $this->is_rtf && $this->action === 'detain';
    }

    /**
     * Check if this is a lift action
     */
    public function isLifted(): bool
    {
        return $this->action === 'lift';
    }
}
