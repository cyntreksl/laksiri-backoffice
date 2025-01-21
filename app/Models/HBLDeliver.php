<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

class HBLDeliver extends Model
{
    use HasFactory, SoftDeletes, LogsActivity;

    protected $table = 'hbl_delivers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'branch_id',
        'hbl_id',
        'driver_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logAll()->logOnlyDirty();
    }

    /**
     * Define the relationship with the HBL model.
     */
    public function hbl()
    {
        return $this->belongsTo(Hbl::class, 'hbl_id');
    }

    /**
     * Define the relationship with the Driver model.
     */
    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }
}
