<?php

namespace App\Actions\Container\Loading;

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

                $hbl = HBL::find($package['hbl_id']);

                UpdateHBLSystemStatus::run($hbl, 4.1);

                MarkAsLoaded::run($package['id']);
            }

            UpdateContainerStatus::run($container, ContainerStatus::DRAFT->value);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to create draft loaded container: '.$e->getMessage());
        }
    }
}
