<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use App\Observers\HBLObserver;
use App\Traits\HasStatusLogs;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy(BranchScope::class)]
#[ObservedBy([HBLObserver::class])]
class HBL extends Model
{
    use HasFactory;
    use HasStatusLogs;
    use LogsActivity;
    use SoftDeletes;

    protected $table = 'hbl';

    public const SYSTEM_STATUS_HBL_PREPARATION_BY_DRIVER = 1.3;

    public const SYSTEM_STATUS_HBL_PREPARATION_BY_WAREHOUSE = 2.1;

    public const SYSTEM_STATUS_CASH_RECEIVED = 2.2;

    public const SYSTEM_STATUS_MANIFEST_PREPARATION = 2.3;

    public const SYSTEM_STATUS_D2D_DOC_PREPARATION = 2.4;

    public const SYSTEM_STATUS_PALLETIZE_CARGO = 2.5;

    protected $SYSTEM_STATUS = [
        3.0 => 'HBL created',
        3.1 => 'HBL created - Job Converted to HBL',
        4.0 => 'Cash Collected',
        4.1 => 'Partial Loaded',
        4.2 => 'Fully Loaded',
        4.3 => 'Partial Unloaded',
        4.4 => 'Fully Unloaded',
    ];

    protected $fillable = [
        'reference', 'warehouse_zone_id', 'cargo_type', 'hbl_type', 'hbl', 'hbl_name', 'email', 'contact_number', 'nic', 'iq_number', 'address', 'consignee_name', 'consignee_nic', 'consignee_contact', 'consignee_address', 'consignee_note', 'warehouse', 'freight_charge', 'bill_charge', 'other_charge', 'discount', 'paid_amount', 'grand_total', 'status', 'created_by', 'branch_id', 'system_status', 'pickup_id', 'is_hold', 'is_short_loading', 'shipper_id', 'consignee_id',
    ];

    public function scopeCashSettlement(Builder $query): void
    {
        $query->whereIn('system_status', [
            self::SYSTEM_STATUS_HBL_PREPARATION_BY_DRIVER,
            self::SYSTEM_STATUS_HBL_PREPARATION_BY_WAREHOUSE,
        ]);
    }

    public function status(): HasMany
    {
        return $this->hasMany(HBLStatusChange::class, 'hbl_id', 'id');
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

    public function packages(): HasMany
    {
        return $this->hasMany(HBLPackage::class, 'hbl_id', 'id');
    }

    public function unloadedPackagesWithoutGlobalScope(): HasMany
    {
        return $this->hasMany(HBLPackage::class, 'hbl_id', 'id')
            ->withoutGlobalScope(BranchScope::class)
            ->where('is_unloaded', true);
    }

    /**
     * Get the user who created this HBL.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function hblPayment(): HasOne
    {
        return $this->hasOne(HBLPayment::class, 'hbl_id', 'id');
    }

    public function scopeWarehouse(Builder $query): void
    {
        $query->whereIn('system_status', [
            self::SYSTEM_STATUS_CASH_RECEIVED, 4.1,
        ]);
    }

    public function warehouseZone(): BelongsTo
    {
        return $this->belongsTo(WarehouseZone::class, 'warehouse_zone_id');
    }

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function hblDocuments(): HasMany
    {
        return $this->hasMany(HBLDocument::class, 'hbl_id', 'id');
    }

    public function pickup(): BelongsTo
    {
        return $this->belongsTo(Pickup::class);
    }

    public function shipper(): BelongsTo
    {
        return $this->belongsTo(User::class, 'shipper_id');
    }

    public function consignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'consignee_id');
    }

    public function unloadingIssues(): HasManyThrough
    {
        return $this->hasManyThrough(UnloadingIssue::class, HBLPackage::class, 'hbl_id', 'hbl_package_id');
    }
}
