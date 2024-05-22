<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy(BranchScope::class)]
class PickUp extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $fillable = [
        'reference',
        'branch_id',
        'cargo_type',
        'name',
        'email',
        'contact_number',
        'address',
        'location_name',
        'location_longitude',
        'location_latitude',
        'zone_id',
        'notes',
        'driver_id',
        'driver_assigned_at',
        'hbl_id',
        'created_by',
        'pickup_date',
        'pickup_time_start',
        'pickup_time_end',
        'is_urgent_pickup',
        'is_from_important_customer',
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
}
