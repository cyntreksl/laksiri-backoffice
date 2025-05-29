<?php

namespace App\Repositories;

use App\Actions\Container\UpdateContainer;
use App\Actions\VesselSchedule\AddContainerToVesselSchedule;
use App\Actions\VesselSchedule\DownloadVesselSchedule;
use App\Actions\VesselSchedule\GetAllVesselSchedules;
use App\Actions\VesselSchedule\GetRecentVesselSchedule;
use App\Actions\VesselSchedule\RemoveContainerFromVesselSchedule;
use App\Interfaces\VesselScheduleRepositoryInterface;
use App\Models\Container;
use App\Models\HBL;
use App\Models\Scopes\BranchScope;
use App\Models\VesselSchedule;
use Carbon\Carbon;

class VesselScheduleRepository implements VesselScheduleRepositoryInterface
{
    public function getAllVesselSchedules()
    {
        return GetAllVesselSchedules::run();
    }

    public function getRecentVesselSchedule()
    {
        return GetRecentVesselSchedule::run();
    }

    public function addVesselToSchedule(VesselSchedule $vesselSchedule, string $vesselReference)
    {
        $container = Container::where('reference', $vesselReference)->first();
        $is_scheduled_container = $vesselSchedule->scheduleContainers()
            ->where('container_id', $container->id)
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
        } elseif ($is_scheduled_container) {
            return response()->json([
                'errors' => [
                    'reference' => ['Container is already scheduled.'],
                ],
            ], 422);
        } else {
            return AddContainerToVesselSchedule::run($vesselSchedule->first(), $container);
        }
    }

    public function removeVesselFromSchedule(VesselSchedule $vesselSchedule, int $containerId)
    {
        return RemoveContainerFromVesselSchedule::run($vesselSchedule, $containerId);
    }

    public function downloadVesselSchedulePDF(VesselSchedule $vesselSchedule)
    {
        return DownloadVesselSchedule::run($vesselSchedule);
    }

    public function updateContainer(Container $container, array $data)
    {
        try {
            $updateData['is_reached'] = $data['is_reached'];
            $updateData['reached_date'] = $data['reached_date'];
            $updateData['note'] = $data['note'];
            $updateData['is_returned'] = $data['is_returned'];
            UpdateContainer::run($container, $updateData);

            $uniqueHbls = collect();
            $processedHblIds = [];
            foreach ($container->hbl_packages as $package) {
                // Only process each HBL once based on its ID
                if (! in_array($package->hbl_id, $processedHblIds)) {
                    $hbl = HBL::withoutGlobalScope(BranchScope::class)->find($package->hbl_id);
                    $uniqueHbls->push($hbl);
                    $processedHblIds[] = $package->hbl_id;
                }
            }
            if ($data['is_reached']) {
                foreach ($uniqueHbls as $hbl) {
                    $hbl->addStatus('Container Arrival', $container->estimated_time_of_arrival);
                }
            }
            if ($data['is_returned']) {
                foreach ($uniqueHbls as $hbl) {
                    $hbl->addStatus('Shipment return by clearance team', Carbon::now());
                }
            }
        } catch (\Exception $e) {
            throw new \Exception('Failed to update container: '.$e->getMessage());
        }
    }
}
