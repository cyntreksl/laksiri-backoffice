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
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy(BranchScope::class)]
#[ObservedBy([HBLPackageObserver::class])]
class HBLPackage extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'hbl_packages';

    protected $fillable = [
        'branch_id',
        'hbl_id',
        'package_type',
        'length',
        'width',
        'height',
        'quantity',
        'weight',
        'actual_weight',
        'volumetric_weight',
        'volume',
        'remarks',
        'is_loaded',
        'is_unloaded',
        'package_rule',
        'measure_type',
        'current_warehouse',
        'is_de_loaded',
        'is_de_unloaded',
        'auto_weight_updated',
        'loaded_at',
        'unloaded_at',
        'loaded_by',
        'unloaded_by',
        'airport_of_departure',
        'airport_of_arrival',
        'is_hold',
        'is_rtf',
        'is_shortland',
        'is_shortland_fixed',
        'shortland_marked_at',
        'shortland_fixed_at',
        'shortland_marked_by',
        'bond_storage_number',
        'release_status',
        'released_at',
        'released_by',
        'release_note',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'auto_weight_updated' => 'boolean',
            'is_hold' => 'boolean',
            'is_rtf' => 'boolean',
            'is_shortland' => 'boolean',
            'is_shortland_fixed' => 'boolean',
            'loaded_at' => 'datetime',
            'unloaded_at' => 'datetime',
            'released_at' => 'datetime',
        ];
    }

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
            ->withPivot('status', 'loaded_by', 'unloaded_by')
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

    public function rtfRecords(): MorphMany
    {
        return $this->morphMany(DetainRecord::class, 'rtfable');
    }

    public function latestRtfRecord(): MorphOne
    {
        return $this->morphOne(DetainRecord::class, 'rtfable')->latestOfMany();
    }

    public function detainRecords(): MorphMany
    {
        return $this->morphMany(DetainRecord::class, 'rtfable');
    }

    public function latestDetainRecord(): MorphOne
    {
        return $this->morphOne(DetainRecord::class, 'rtfable')->latestOfMany();
    }

    public function remarks(): MorphMany
    {
        return $this->morphMany(Remark::class, 'remarkable');
    }

    public function releasedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    public function packageExaminations(): HasMany
    {
        return $this->hasMany(PackageExamination::class, 'hbl_package_id');
    }

    public function latestPackageExamination()
    {
        return $this->hasOne(PackageExamination::class, 'hbl_package_id')->latestOfMany();
    }
}
