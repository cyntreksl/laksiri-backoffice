<?php

namespace App\Actions\Container\Loading;

use App\Actions\Container\UpdateContainerStatus;
use App\Actions\HBL\HBLPackage\MarkAsUnloaded;
use App\Actions\HBL\UpdateHBLSystemStatus;
use App\Enum\ContainerStatus;
use App\Models\Container;
use App\Models\HBL;
use App\Models\HBLPackage;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class DeleteDraftLoadedContainer
{
    use AsAction;

    /**
     * @throws \Exception
     */
    public function handle(array $data)
    {
        try {
            $container = Container::find($data['container_id']);

            DB::beginTransaction();

            $container->hbl_packages()->detach($data['package_id']);

            $container->duplicate_hbl_packages()->detach($data['package_id']);

            MarkAsUnloaded::run($data['package_id']);

            $package = HBLPackage::find($data['package_id']);

            $hbl = HBL::find($package->hbl_id);

            $isPartialLoaded = $container->hbl_packages()
                ->wherePivotIn('hbl_package_id', $hbl->packages->pluck('id'))
                ->exists();

            if (! $isPartialLoaded) {
                UpdateHBLSystemStatus::run($hbl, HBL::SYSTEM_STATUS_CASH_RECEIVED);
            }

            // update the container status as a 'requested' when full loading removed.
            if (! $container->hbl_packages()->exists()) {
                UpdateContainerStatus::run($container, ContainerStatus::REQUESTED->value);
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to delete draft loaded container: '.$e->getMessage());
        }
    }
}
