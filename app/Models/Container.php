<?php

namespace App\Models;

use App\Enum\ContainerStatus;
use App\Models\Scopes\BranchScope;
use App\Observers\ContainerObserver;
use App\Traits\HasStatusLogs;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy(BranchScope::class)]
#[ObservedBy([ContainerObserver::class])]
class Container extends Model
{
    use HasFactory;
    use HasStatusLogs;
    use LogsActivity;
    use SoftDeletes;

    public const SYSTEM_STATUS_BOOK_CONTAINER = 3.1;

    public const SYSTEM_STATUS_CONTAINER_LOADING = 3.2;

    public const SYSTEM_STATUS_CONTAINER_SHIPPED = 3.3;

    public const SYSTEM_STATUS_CONTAINER_TRANSIT_TIME = 3.4;

    public const SYSTEM_STATUS_CONTAINER_ARRIVAL = 3.5;

    public const SYSTEM_STATUS_CONTAINER_RETURNED = 5.1;

    public const SYSTEM_STATUS_CONTAINER_CLEARED = 4.2;

    public const SYSTEM_STATUS_CONTAINER_TRANSPORTED = 4.3;

    public const SYSTEM_STATUS_CONTAINER_DE_STUFFING_AT_WAREHOUSE = 4.4;

    protected $fillable = [
        'branch_id', 'target_warehouse', 'cargo_type', 'air_line_id', 'container_type', 'reference', 'bl_number', 'awb_number', 'container_number', 'seal_number', 'maximum_volume', 'minimum_volume', 'maximum_weight', 'minimum_weight', 'maximum_volumetric_weight', 'minimum_volumetric_weight', 'estimated_time_of_departure', 'estimated_time_of_arrival', 'vessel_name', 'voyage_number', 'shipping_line', 'port_of_loading', 'port_of_discharge', 'flight_number', 'airline_name', 'airport_of_departure', 'airport_of_arrival', 'cargo_class', 'status', 'system_status', 'loading_started_at', 'loading_ended_at', 'unloading_started_at', 'unloading_ended_at', 'loading_started_by', 'loading_ended_by', 'unloading_started_by', 'unloading_ended_by', 'created_by', 'note', 'is_reached', 'reached_date', 'shipment_weight',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

    public function hbl_packages(): BelongsToMany
    {
        return $this->belongsToMany(HBLPackage::class, 'container_hbl_package', 'container_id', 'hbl_package_id')
            ->withPivot('status', 'loaded_by')
            ->withTimestamps();
    }

    public function duplicate_hbl_packages(): BelongsToMany
    {
        return $this->belongsToMany(HBLPackage::class, 'duplicate_container_hbl_package', 'container_id', 'hbl_package_id')
            ->withPivot('status', 'loaded_by')
            ->withTimestamps();
    }

    public function scopeLoadedContainers(Builder $query): void
    {
        $query->where('status', ContainerStatus::LOADED->value);
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function containerDocuments(): HasMany
    {
        return $this->hasMany(ContainerDocument::class, 'container_id', 'id');
    }

    public function handlingProcedures()
    {
        return $this->hasMany(HandlingProcedure::class);
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'target_warehouse');
    }

    public function payment(): HasOne
    {
        return $this->HasOne(ContainerPayment::class, 'container_id', 'id');
    }
}
