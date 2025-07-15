<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command(\App\Console\Commands\VesselScheduleGenerator::class)->cron('0 0 * * 1');
Schedule::command(\App\Console\Commands\CalculateDoChargeSchedule::class)->dailyAt('01:00');
Schedule::command(\App\Console\Commands\CalculateDemurrageChargeSchedule::class)->dailyAt('02:00');
