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
class PackagePrice extends Model
{
    use HasFactory;
    use SoftDeletes;

    use LogsActivity;

    protected $table = 'branch_package_prices';

    protected $fillable = [
        'branch_id', 'destination_branch_id', 'cargo_mode', 'hbl_type', 'rule_title', 'length', 'width', 'height',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }
}
