<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class VesselSchedule extends Model
{
    use HasFactory;

    protected $table = 'vessel_schedule';

    protected $fillable = [
        'start_date',
        'end_date',
    ];

    public function scheduleContainers(): HasMany
    {
        return $this->hasMany(VesselScheduleContainer::class, 'vessel_schedule_id', 'id');
    }

    public function containers(): HasManyThrough
    {
        return $this->hasManyThrough(
            Container::class,
            VesselScheduleContainer::class,
            'vessel_schedule_id',
            'id',
            'id',
            'container_id'
        );
    }
}
