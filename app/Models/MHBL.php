<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class Mhbl extends Model
{
    use HasFactory, SoftDeletes;
    use LogsActivity;

    protected $table = 'mhbls';

    protected $fillable = [
        'reference',
        'branch_id',
        'created_by',
        'consignee_id',
        'shipper_id',
        'cargo_type',
        'warehouse_id',
        'grand_volume',
        'grand_weight',
        'grand_total',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

    public function hbls(): HasMany
    {
        return $this->hasMany(MHBLsHBL::class, 'mhbl_id', 'id');
    }
}
