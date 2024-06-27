<?php

namespace App\Models;

use App\Actions\User\GetUserCurrentBranchID;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;

class Area extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'branch_id',
    ];

    public function zones(): BelongsToMany
    {
        return $this->belongsToMany(Zone::class)->withTimestamps();
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class);
    }

    public function scopeBranch(Builder $query)
    {
        $query->where('branch_id', GetUserCurrentBranchID::run());
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }
}
