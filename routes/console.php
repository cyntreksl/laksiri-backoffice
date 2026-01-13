<?php

use Illuminate\Support\Facades\Schedule;

// Schedule the token due marking command to run at midnight every day
Schedule::command('tokens:mark-due')
    ->dailyAt('23:59')
    ->withoutOverlapping()
    ->runInBackground();
