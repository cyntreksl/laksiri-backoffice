<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy(BranchScope::class)]
class Container extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $fillable = [
        'branch_id',
        'cargo_type',
        'reference',
        'bl_number',
        'awb_number',
        'container_number',
        'seal_number',
        'vessel_name',
        'voyage_number',
        'shipping_line',
        'port_of_loading',
        'port_of_discharge',
        'flight_number',
        'airline_name',
        'airport_of_departure',
        'airport_of_arrival',
        'estimated_time_of_departure',
        'estimated_time_of_arrival',
        'cargo_class',
        'status',
        'system_status',
        'loading_started_at',
        'loading_ended_at',
        'unloading_started_at',
        'unloading_ended_at',
        'loading_started_by',
        'loading_ended_by',
        'unloading_started_by',
        'unloading_ended_by',
        'created_by',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }
}
