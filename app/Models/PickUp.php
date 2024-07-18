<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use App\Traits\HasStatusLogs;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy(BranchScope::class)]
class PickUp extends Model
{
    use HasFactory;
    use HasStatusLogs;
    use LogsActivity;
    use SoftDeletes;

    public const SYSTEM_STATUS_INQUIRY = 1.1;

    public const SYSTEM_STATUS_PICKUP_CREATED = 1.2;

    public const SYSTEM_STATUS_DRIVER_ASSIGNED = 1.3;

    public const SYSTEM_STATUS_CARGO_COLLECTED = 1.4;

    public const SYSTEM_STATUS_CARGO_RETURNED = 1.5;

    protected $fillable = [
        'reference', 'cargo_type', 'name', 'email', 'contact_number', 'address', 'location_name', 'location_longitude', 'location_latitude', 'zone_id', 'notes', 'driver_id', 'driver_assigned_at', 'hbl_id', 'created_by', 'deleted_at', 'branch_id', 'pickup_date', 'pickup_time_start', 'pickup_time_end', 'is_urgent_pickup', 'is_from_important_customer', 'pickup_order', 'system_status', 'status', 'pickup_type', 'pickup_note',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

    public function scopeAssignedToDriver(Builder $query): void
    {
        $query->where('driver_id', auth()->id());
    }

    /**
     * Get the zone that owns the pickup.
     */
    public function zone(): BelongsTo
    {
        return $this->belongsTo(Zone::class, 'zone_id');
    }

    /**
     * Get the driver that owns the pickup.
     */
    public function driver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function pickupException(): HasMany
    {
        return $this->hasMany(PickupException::class, 'pickup_id');
    }

    public function hbl()
    {
        return $this->belongsTo(HBL::class, 'hbl_id');
    }
}
