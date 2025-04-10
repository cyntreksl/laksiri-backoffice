<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class SpecialDOCharge extends Model
{
    use HasFactory, LogsActivity, SoftDeletes;

    protected $table = 'special_do_charges';

    protected $fillable = [
        'branch_id',
        'agent_id',
        'hbl_type',
        'collected',
        'condition',
        'quantity_basis',
        'package_type',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

}
