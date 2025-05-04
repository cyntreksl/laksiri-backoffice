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
class Tax extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $fillable = [
        'branch_id',
        'name',
        'rate',
        'is_active',
        'created_by',
    ];

    protected $casts = [
        'rate' => 'float',
        'is_active' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }
}

namespace App\Actions\Tax;

use App\Models\Scopes\BranchScope;
use App\Models\Tax;
use Lorisleiva\Actions\Concerns\AsAction;

class GetTaxByWarehouse
{
    use AsAction;

    public function handle(int $warehouseID): Tax
    {
        return Tax::withoutGlobalScope(BranchScope::class)->where('branch_id', $warehouseID)->where('is_active', '=', true)->first();
    }
}
