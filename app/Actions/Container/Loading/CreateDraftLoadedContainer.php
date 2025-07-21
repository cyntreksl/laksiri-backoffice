<?php

namespace App\Actions\Container\Loading;

use App\Actions\Container\UpdateContainer;
use App\Actions\Container\UpdateContainerStatus;
use App\Actions\HBL\HBLPackage\MarkAsLoaded;
use App\Actions\HBL\UpdateHBLSystemStatus;
use App\Enum\ContainerStatus;
use App\Models\Container;
use App\Models\HBL;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateDraftLoadedContainer
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
            $data = [
                'loading_started_at' => now(),
                'loading_started_by' => auth()->id(),
            ];

            UpdateContainer::run($container, $data);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to create draft loaded container: '.$e->getMessage());
        }
    }
}
