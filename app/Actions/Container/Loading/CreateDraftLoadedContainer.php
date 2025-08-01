<?php

namespace App\Actions\Container\Loading;

use App\Actions\Container\UpdateContainer;
use App\Actions\Container\UpdateContainerStatus;
use App\Actions\HBL\HBLPackage\MarkAsLoaded;
use App\Actions\HBL\UpdateHBLSystemStatus;
use App\Enum\ContainerStatus;
use App\Models\Container;
use App\Models\HBL;
use App\Traits\HandlesDeadlocks;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateDraftLoadedContainer
{
    use AsAction, HandlesDeadlocks;

    /**
     * @throws \Exception
     */
    public function handle(array $data)
    {
        return $this->transactionWithDeadlockRetry(function () use ($data) {
            $container = Container::find($data['container_id']);

            foreach ($data['packages'] as $package) {
                $container->hbl_packages()->attach($package['id'], [
                    'status' => 'draft',
                    'loaded_by' => auth()->id(),
                ]);
                $container->duplicate_hbl_packages()->attach($package['id'], [
                    'status' => 'draft',
                    'loaded_by' => auth()->id(),
                ]);

                $hbl = HBL::find($package['hbl_id']);

                if ($package['is_unloaded']) {
                    $hbl->addStatus('Container Loading in '.session('current_branch_name'));
                } else {
                    $hbl->addStatus('Container Loading');
                }

                UpdateHBLSystemStatus::run($hbl, HBL::SYSTEM_STATUS_PARTIAL_LOADED);

                MarkAsLoaded::run($package['id']);
            }

            UpdateContainerStatus::run($container, ContainerStatus::DRAFT->value);

            // update container loading start datetime and who loaded by
            $updateData = [
                'loading_started_at' => now(),
                'loading_started_by' => auth()->id(),
            ];

            UpdateContainer::run($container, $updateData);
        }, 3, 100); // 3 retries with 100ms base delay
    }
}
