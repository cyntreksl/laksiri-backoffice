<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy(BranchScope::class)]
class HBL extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $table = 'hbl';

    protected $SYSTEM_STATUS = [
        3.0 => 'HBL created',
        3.1 => 'HBL created - Job Converted to HBL',
        4.0 => 'Cash Collected',
        4.1 => 'Partial Loaded',
        4.2 => 'Full Loaded',
    ];

    protected $fillable = [
        'reference', 'branch_id', 'pickup_id', 'cargo_type', 'hbl_type', 'hbl', 'hbl_name', 'email', 'contact_number', 'nic', 'iq_number', 'address', 'consignee_name', 'consignee_nic', 'consignee_contact', 'consignee_address', 'consignee_note', 'warehouse', 'freight_charge', 'bill_charge', 'other_charge', 'discount', 'paid_amount', 'grand_total', 'created_by', 'deleted_at',
    ];

    public function scopeCashSettlement(Builder $query)
    {
        $query->where('system_status', 3.1);
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

    public function scopeWarehouse(Builder $query)
    {
        $query->where('system_status', 4);
    }

    // actions
    //    public function isFullyLoaded(): bool
    //    {
    //        return $this->packages()->whereHas('containers', function ($query) {
    //            $query->where('status', 'loaded');
    //        })->count() === $this->packages()->count();
    //    }
    //
    //    public function isPartiallyLoaded(): bool
    //    {
    //        $totalPackages = $this->packages()->count();
    //        $loadedPackages = $this->packages()->whereHas('containers', function ($query) {
    //            $query->where('status', 'loaded');
    //        })->count();
    //
    //        return $loadedPackages > 0 && $loadedPackages < $totalPackages;
    //    }
}
