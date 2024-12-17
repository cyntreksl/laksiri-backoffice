<?php

namespace App\Models;

use App\Models\Scopes\BranchScope;
use App\Traits\HasFile;
use Illuminate\Database\Eloquent\Attributes\ScopedBy;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

#[ScopedBy(BranchScope::class)]
class Officer extends Model
{
    use HasFactory;
    use HasFile;
    use LogsActivity;
    use SoftDeletes;

    protected $table = 'officers';

    protected $fillable = [
        'branch_id', 'type', 'name', 'email', 'mobile_number', 'pp_or_nic_no', 'residency_no', 'address',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }
}
