<?php

namespace App\Actions\Container\Unloading;

use App\Actions\Container\UpdateContainer;
use App\Actions\Container\UpdateContainerStatus;
use App\Actions\HBL\HBLPackage\MarkAsFullyUnloaded;
use App\Actions\HBL\HBLPackage\UpdateHBLPackage;
use App\Enum\ContainerStatus;
use App\Models\Container;
use App\Models\HBL;
use App\Models\HBLPackage;
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

            // Group packages by HBL ID
            $hblGroups = collect($data['packages'])->groupBy('hbl.id');

            // Assign bond storage numbers
            AssignBondStorageNumber::run($hblGroups);

            foreach ($data['packages'] as $package) {
                $container->hbl_packages()->detach($package['id']);

                $container->duplicate_hbl_packages()->updateExistingPivot($package['id'], [
                    'status' => 'unloaded',
                ]);

                // Run the MarkAsUnloaded action for the package ID
                MarkAsFullyUnloaded::run($package['id']);

                $hbl_package = HBLPackage::find($package['id']);

                UpdateHBLPackage::run($hbl_package, [
                    'unloaded_by' => auth()->id(),
                    'unloaded_at' => now(),
                ]);
            }

            $hbls = collect($data['packages'])
                ->pluck('hbl')
                ->unique('id')
                ->values();

            foreach ($hbls as $hbl) {
                $hbl = HBL::find($hbl['id']);

                $hbl->addStatus('Container Unloaded in '.session('current_branch_name'));
            }

            UpdateContainerStatus::run($container, ContainerStatus::UNLOADED->value);

            $container->addStatus('Container cleared');

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
