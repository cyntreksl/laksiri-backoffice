<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

#[ScopedBy(BranchScope::class)]
class HBLPackage extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $table = 'hbl_packages';

    protected $fillable = [
        'hbl_id',
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
        'remarks',
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

    public function unloadingIssue(): HasOne
    {
        return $this->hasOne(UnloadingIssue::class, 'hbl_package_id', 'id');
    }
}
