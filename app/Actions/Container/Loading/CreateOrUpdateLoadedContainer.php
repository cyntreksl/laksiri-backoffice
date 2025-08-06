<?php

namespace App\Actions\Container\Loading;

use App\Actions\Container\UpdateContainer;
use App\Actions\Container\UpdateContainerStatus;
use App\Actions\HBL\HBLPackage\MarkAsLoaded;
use App\Actions\HBL\HBLPackage\UpdateHBLPackage;
use App\Actions\HBL\UpdateHBLSystemStatus;
use App\Enum\ContainerStatus;
use App\Models\Container;
use App\Models\HBL;
use App\Models\HBLPackage;
use App\Traits\HandlesDeadlocks;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateOrUpdateLoadedContainer
{
    use AsAction, HandlesDeadlocks;

    /**
     * @throws \Exception
     */
    public function handle(array $data)
    {
        return $this->transactionWithDeadlockRetry(function () use ($data) {
            $container = Container::find($data['container_id']);

            $isDestinationLoading = $data['isDestinationLoading'] ?? false;

            foreach ($data['packages'] as $package) {
                $result = $container->hbl_packages()->updateExistingPivot($package['id'], [
                    'status' => 'loaded',
                    'loaded_by' => auth()->id(),
                ]);

                $container->duplicate_hbl_packages()->updateExistingPivot($package['id'], [
                    'status' => 'loaded',
                    'loaded_by' => auth()->id(),
                ]);

                if (! $result) {
                    $container->hbl_packages()->attach($package['id'], [
                        'status' => 'loaded',
                        'loaded_by' => auth()->id(),
                    ]);
                    $container->duplicate_hbl_packages()->attach($package['id'], [
                        'status' => 'loaded',
                        'loaded_by' => auth()->id(),
                    ]);
                }

                // Run the MarkAsLoaded action for the package ID
                MarkAsLoaded::run($package['id'], $isDestinationLoading);

                $hbl_package = HBLPackage::find($package['id']);

                UpdateHBLPackage::run($hbl_package, [
                    'loaded_by' => auth()->id(),
                    'loaded_at' => now(),
                    'airport_of_departure' => $container?->airport_of_departure,
                    'airport_of_arrival' => $container?->airport_of_arrival,
                ]);

                $hbl = HBL::find($hbl_package->hbl_id);

                UpdateHBLSystemStatus::run($hbl, HBL::SYSTEM_STATUS_FULLY_LOADED);

                $hbl->addStatus('HBL Loaded Into Shipment');
            }

            UpdateContainerStatus::run($container, ContainerStatus::LOADED->value);

            $container->addStatus('Container Loaded');
            $container->addStatus('Container Shipped');

            // update container loading end datetime and who loaded by
            $updateData = [
                'loading_ended_at' => now(),
                'loading_ended_by' => auth()->id(),
                'note' => $data['note'] ?? null,
            ];

            $container = UpdateContainer::run($container, $updateData);

            return $container;
        }, 3, 100); // 3 retries with 100ms base delay
    }
}
