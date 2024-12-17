<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
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

    public function hbls(): HasManyThrough
    {
        return $this->hasManyThrough(
            HBL::class,
            MHBLsHBL::class,
            'mhbl_id',
            'id',
            'id',
            'hbl_id'
        );
    }

    public function shipper(): HasOne
    {
        return $this->hasOne(Officer::class, 'id', 'shipper_id')
            ->where('type', 'shipper');
    }

    public function warehouse(): HasOne
    {
        return $this->hasOne(Branch::class, 'id', 'warehouse_id');
    }

    public function consignee(): HasOne
    {
        return $this->hasOne(Officer::class, 'id', 'consignee_id')
            ->where('type', 'consignee');
    }
}
