<?php

namespace App\Actions\Container\Unloading;

use App\Actions\Container\UpdateContainer;
use App\Actions\HBL\UpdateHBLSystemStatus;
use App\Models\Container;
use App\Models\HBL;
use App\Models\Scopes\BranchScope;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateDraftUnload
{
    use AsAction;

    /**
     * @throws \Exception
     */
    public function handle(array $data)
    {
        try {
            $container = Container::withoutGlobalScope(BranchScope::class)->find($data['container_id']);

            DB::beginTransaction();

            foreach ($data['packages'] as $package) {

                $container->hbl_packages()->updateExistingPivot($package['id'], [
                    'status' => 'draft-unload',
                    'unloaded_by' => auth()->id(),
                ]);

                $container->duplicate_hbl_packages()->updateExistingPivot($package['id'], [
                    'status' => 'draft-unload',
                    'unloaded_by' => auth()->id(),
                ]);

                $hbl = HBL::withoutGlobalScope(BranchScope::class)->find($package['hbl_id']);

                UpdateHBLSystemStatus::run($hbl, 4.3);
            }

            // update container loading start datetime and who loaded by
            $data = [
                'unloading_started_at' => now(),
                'unloading_started_by' => auth()->id(),
            ];

            UpdateContainer::run($container, $data);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to create draft loaded container: '.$e->getMessage());
        }
    }
}
