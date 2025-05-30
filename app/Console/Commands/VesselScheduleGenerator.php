<?php

namespace App\Console\Commands;

use App\Enum\CargoType;
use App\Enum\ContainerStatus;
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
    protected $description = 'Generate a vessel schedule with containers arriving this week';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        // Get the current week's start (Monday) and end (Sunday)
        $weekStart = Carbon::now()->startOfWeek();
        $weekEnd = Carbon::now()->endOfWeek();

        // Query containers arriving this week
        $containers = Container::withoutGlobalScopes()
            ->where('estimated_time_of_arrival', '<=', $weekEnd->format('Y-m-d'))
            ->where('cargo_type', '=', CargoType::SEA_CARGO->value)
            ->where('is_reached', false)
            ->where('status', '=', ContainerStatus::IN_TRANSIT->value)
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
