<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command(\App\Console\Commands\VesselScheduleGenerator::class)->cron('0 0 * * 1');
