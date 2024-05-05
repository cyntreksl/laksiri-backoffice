<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }
}
