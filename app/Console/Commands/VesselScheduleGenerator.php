<?php

namespace App\Console\Commands;

use App\Models\Container;
use App\Models\VesselSchedule;
use Carbon\Carbon;
use Illuminate\Console\Command;

class VesselScheduleGenerator extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:vessel-schedule-generator';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get current week's start (Monday) and end (Sunday)
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        // Query containers arriving this week
        $containers = Container::withoutGlobalScopes()
            ->where('estimated_time_of_arrival', '<=', $weekEnd->format('Y-m-d'))
            ->where('cargo_type', '=', 'Sea Cargo')
            ->where('is_reached', false)
            ->get();

        $vesselSchedule = VesselSchedule::create([
            'start_date' => $weekStart,
            'end_date' => $weekEnd,
        ]);

        foreach ($containers as $container) {
            $vesselSchedule->scheduleContainers()->create([
                'container_id' => $container['id'],
            ]);
        }
    }
}
