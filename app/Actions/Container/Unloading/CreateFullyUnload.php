<?php

namespace App\Actions\Container\Unloading;

use App\Actions\Container\UpdateContainer;
use App\Actions\Container\UpdateContainerStatus;
use App\Actions\HBL\HBLPackage\MarkAsFullyUnloaded;
use App\Enum\ContainerStatus;
use App\Models\Container;
use App\Models\Scopes\BranchScope;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateFullyUnload
{
    use AsAction;

    /**
     * @throws \Exception
     */
    public function handle(array $data)
    {

        try {
            DB::beginTransaction();

            $container = Container::withoutGlobalScope(BranchScope::class)
                ->find($data['container_id']);

            foreach ($data['packages'] as $package) {
                $container->hbl_packages()->detach($package['id']);

                // Run the MarkAsUnloaded action for the package ID
                MarkAsFullyUnloaded::run($package['id']);
            }

            UpdateContainerStatus::run($container, ContainerStatus::UNLOADED->value);

            // update container loading end datetime and who loaded by
            $data = [
                'unloading_ended_at' => now(),
                'unloading_ended_by' => auth()->id(),
            ];

            UpdateContainer::run($container, $data);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to create loaded container: '.$e->getMessage());
        }
    }
}
