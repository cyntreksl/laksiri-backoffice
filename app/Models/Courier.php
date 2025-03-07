<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use App\Traits\HasQueueLogs;
use App\Traits\HasStatusLogs;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy(BranchScope::class)]
class Courier extends Model
{
    use HasFactory;
    use HasQueueLogs;
    use HasStatusLogs;
    use LogsActivity;
    use SoftDeletes;

    protected $table = 'couriers';

    public const PENDING = 'pending';

    public const ON_COURIER = 'on courier';

    public const DELIVERED = 'delivered';

    protected $fillable = [
        'branch_id',
        'courier_number',
        'cargo_type',
        'hbl_type',
        'courier_agent',
        'name',
        'email',
        'contact_number',
        'nic',
        'iq_number',
        'address',
        'consignee_name',
        'consignee_nic',
        'consignee_contact',
        'consignee_address',
        'consignee_note',
        'status',
        'created_by',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

    public function packages(): HasMany
    {
        return $this->hasMany(CourierPackage::class, 'courier_id', 'id');
    }
}
