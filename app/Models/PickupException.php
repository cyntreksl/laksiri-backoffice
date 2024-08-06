<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy(BranchScope::class)]
class PickupException extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'pickup_id',
        'branch_id',
        'driver_id',
        'exception_name_id',
        'zone_id',
        'reference',
        'name',
        'address',
        'pickup_date',
        'auth',
        'system_status',
        'driver_assigned_at',
        'created_by',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

    /**
     * Get the zone that owns the pickup.
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function scopeAssignedToDriver(Builder $query): void
    {
        $query->where('system_status', PickUp::SYSTEM_STATUS_DRIVER_ASSIGNED)
            ->where('driver_id', auth()->id());
    }

    public function exceptionType(): HasOne
    {
        return $this->hasOne(ExceptionName::class, 'id', 'exception_name_id');
    }
}
