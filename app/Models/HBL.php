<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use App\Observers\HBLObserver;
use App\Traits\HasQueueLogs;
use App\Traits\HasStatusLogs;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy(BranchScope::class)]
#[ObservedBy([HBLObserver::class])]
class HBL extends Model
{
    use HasFactory;
    use HasQueueLogs;
    use HasStatusLogs;
    use LogsActivity;
    use SoftDeletes;

    protected $table = 'hbl';

    // HBL Preparation
    public const SYSTEM_STATUS_HBL_PREPARATION_BY_DRIVER = 1.3;

    public const SYSTEM_STATUS_HBL_PREPARATION_BY_WAREHOUSE = 2.1;

    // Pre-HBL Processes
    public const SYSTEM_STATUS_CASH_RECEIVED = 2.2;

    public const SYSTEM_STATUS_MANIFEST_PREPARATION = 2.3;

    public const SYSTEM_STATUS_D2D_DOC_PREPARATION = 2.4;

    public const SYSTEM_STATUS_PALLETIZE_CARGO = 2.5;

    // HBL Creation
    public const SYSTEM_STATUS_HBL_CREATED = 3.0;

    public const SYSTEM_STATUS_HBL_CONVERTED_FROM_JOB = 3.1;

    // Post-HBL Processes
    public const SYSTEM_STATUS_CASH_COLLECTED = 4.0;

    public const SYSTEM_STATUS_PARTIAL_LOADED = 4.1;

    public const SYSTEM_STATUS_FULLY_LOADED = 4.2;

    public const SYSTEM_STATUS_PARTIAL_UNLOADED = 4.3;

    public const SYSTEM_STATUS_FULLY_UNLOADED = 4.4;

    public const SYSTEM_STATUS_FINANCE_APPROVED = 4.5;

    // system status for token and queue can be start from 6.0
    // 6.0 is for token issued
    // 6.1 is for reception queue
    // 6.2 is for document verification queue
    // 6.3 is for cashier queue
    // 6.4 is for waiting for package receive from bon area queue
    // 6.6 is for examination queue
    // 6.7 is for gate pass issued
    // 6.8 is for gate pass mark as released

    protected $fillable = [
        'reference',
        'warehouse_zone_id',
        'cargo_type',
        'hbl_type',
        'hbl',
        'hbl_name',
        'email',
        'contact_number',
        'additional_mobile_number',
        'whatsapp_number',
        'nic',
        'iq_number',
        'address',
        'consignee_name',
        'consignee_nic',
        'consignee_contact',
        'consignee_additional_mobile_number',
        'consignee_whatsapp_number',
        'consignee_address',
        'consignee_note',
        'warehouse',
        'warehouse_id',
        'freight_charge',
        'bill_charge',
        'other_charge',
        'destination_charge',
        'discount',
        'additional_charge',
        'paid_amount',
        'grand_total',
        'do_charge',
        'is_departure_charges_paid',
        'is_destination_charges_paid',
        'status',
        'created_by',
        'branch_id',
        'system_status',
        'pickup_id',
        'is_hold',
        'is_short_loading',
        'is_driver_assigned',
        'shipper_id',
        'consignee_id',
        'is_released',
        'hbl_number',
        'cr_number',
        'is_finance_release_approved',
        'finance_release_approved_by',
        'finance_release_approved_date',
        'is_arrived_to_primary_warehouse',
        'currency_rate',
        'package_charges',
    ];

    protected $appends = [
        'branch_name',
    ];

    public function scopeCashSettlement(Builder $query): void
    {
        if (request()->routeIs('back-office.duepayments.duePaymentList') || request()?->type == 'duepayments') {
            $query->whereIn('system_status', [
                self::SYSTEM_STATUS_HBL_PREPARATION_BY_DRIVER,
                self::SYSTEM_STATUS_HBL_PREPARATION_BY_WAREHOUSE,
                self::SYSTEM_STATUS_CASH_RECEIVED,
            ]);
        } else {
            $query->whereIn('system_status', [
                self::SYSTEM_STATUS_HBL_PREPARATION_BY_DRIVER,
                self::SYSTEM_STATUS_HBL_PREPARATION_BY_WAREHOUSE,
            ]);
        }
    }

    public function scopeDuePayment(Builder $query): void
    {
        $query->whereColumn('paid_amount', '<', 'grand_total');
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

    public function hblPayment(): HasMany
    {
        return $this->HasMany(HBLPayment::class, 'hbl_id', 'id');
    }

    public function latestHblPayment(): HasOne
    {
        return $this->hasOne(HBLPayment::class, 'hbl_id', 'id')->latestOfMany();
    }

    public function scopeWarehouse(Builder $query): void
    {
        $query->whereIn('system_status', [
            self::SYSTEM_STATUS_CASH_RECEIVED,
            4.1,
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
        return $this->belongsTo(PickUp::class);
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

    public function getBranchNameAttribute()
    {
        return $this->branch->name;
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(Token::class, 'reference', 'reference')->latest();
    }

    public function callFlags(): HasMany
    {
        return $this->hasMany(CallFlag::class, 'hbl_id');
    }

    public function mhbl(): HasOneThrough
    {
        return $this->hasOneThrough(
            MHBL::class,
            MHBLsHBL::class,
            'hbl_id',
            'id',
            'id',
            'mhbl_id'
        );
    }

    public function slInvoices(): HasOne
    {
        return $this->hasOne(SLInvoice::class, 'hbl_id');
    }

    public function assignedDriver(): HasOneThrough
    {
        return $this->hasOneThrough(
            User::class,
            HBLDeliver::class,
            'hbl_id',
            'id',
            'id',
            'driver_id'
        );
    }

    public function containers(): BelongsToMany
    {
        return $this->belongsToMany(Container::class, 'container_hbl_package', 'hbl_package_id', 'container_id')
            ->withPivot('status', 'loaded_by')
            ->withTimestamps();
    }

    public function rtfRecords(): MorphMany
    {
        return $this->morphMany(RtfRecord::class, 'rtfable');
    }

    public function latestRtfRecord(): MorphOne
    {
        return $this->morphOne(RtfRecord::class, 'rtfable')->latestOfMany();
    }

    public function departureCharge(): HasOne
    {
        return $this->hasOne(HBLDepartureCharge::class, 'hbl_id', 'id');
    }

    public function destinationCharge(): HasOne
    {
        return $this->hasOne(HBLDestinationCharge::class, 'hbl_id', 'id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class, 'hbl_id');
    }
}
