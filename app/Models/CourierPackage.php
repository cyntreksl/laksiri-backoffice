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
class CourierPackage extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'courier_packages'; // Explicitly defining the table name (optional)

    protected $fillable = [
        'branch_id',
        'courier_id',
        'package_type',
        'length',
        'width',
        'height',
        'quantity',
        'weight',
        'volume',
        'remarks',
        'measure_type',
    ];

    /**
     * Relationships
     */

    // A courier package belongs to a branch
    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    // A courier package belongs to a courier
    public function courier()
    {
        return $this->belongsTo(Courier::class);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }
}
