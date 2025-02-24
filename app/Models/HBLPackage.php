<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use App\Observers\HBLPackageObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy(BranchScope::class)]
#[ObservedBy([HBLPackageObserver::class])]
class HBLPackage extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'hbl_packages';

    protected $fillable = [
        'hbl_id',
        'package_rule',
        'branch_id',
        'package_type',
        'length',
        'width',
        'height',
        'quantity',
        'volume',
        'weight',
        'is_loaded',
        'is_unloaded',
        'current_warehouse',
        'is_de_loaded',
        'is_de_unloaded',
        'remarks',
        'measure_type',
    ];

    public function hbl(): BelongsTo
    {
        return $this->belongsTo(HBL::class, 'hbl_id');
    }

    public function containers(): BelongsToMany
    {
        return $this->belongsToMany(Container::class, 'container_hbl_package', 'hbl_package_id', 'container_id')
            ->withPivot('status', 'loaded_by')
            ->withTimestamps();
    }

    public function duplicate_containers(): BelongsToMany
    {
        return $this->belongsToMany(Container::class, 'duplicate_container_hbl_package', 'hbl_package_id', 'container_id')
            ->withPivot('status', 'loaded_by')
            ->withTimestamps();
    }

    public function unloadingIssue()
    {
        return $this->hasMany(UnloadingIssue::class, 'hbl_package_id', 'id');
    }

    public function packageRuleData()
    {
        return $this->hasOne(HBLPackageRuleData::class, 'package_id', 'id');
    }
}
