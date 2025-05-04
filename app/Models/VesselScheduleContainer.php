<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VesselScheduleContainer extends Model
{
    use HasFactory;

    protected $table = 'vessel_schedule_container';

    protected $fillable = [
        'vessel_schedule_id',
        'container_id',
    ];
}
