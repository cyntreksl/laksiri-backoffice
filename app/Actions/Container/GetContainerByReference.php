<?php

namespace App\Actions\Container;

use App\Enum\CargoType;
use App\Enum\ContainerStatus;
use App\Models\Container;
use App\Models\VesselScheduleContainer;
use Lorisleiva\Actions\Concerns\AsAction;

class GetContainerByReference
{
    use AsAction;

    public function handle(string $reference, string|int $vesselScheduleId)
    {
        $container = Container::where('reference', $reference)
            ->with('warehouse')
            ->where('is_reached', false)
            ->where('status', ContainerStatus::IN_TRANSIT->value)
            ->where('cargo_type', '=', CargoType::SEA_CARGO->value)
            ->first();

        if (! $container) {
            return response()->json([
                'errors' => [
                    'reference' => ['Container not found or invalid reference number.'],
                ],
            ], 422);
        } elseif ($container->is_reached) {
            return response()->json([
                'errors' => [
                    'reference' => ['Container is already reached to destination.'],
                ],
            ], 422);
        }

        $is_scheduled_container = VesselScheduleContainer::where('vessel_schedule_id', $vesselScheduleId)
            ->where('container_id', $container->id)
            ->exists();

        if ($is_scheduled_container) {
            return response()->json([
                'errors' => [
                    'reference' => ['Container is already scheduled.'],
                ],
            ], 422);
        } else {
            return response()->json($container);
        }
    }
}
