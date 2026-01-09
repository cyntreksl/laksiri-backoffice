<?php

namespace App\Actions\Container\Unloading;

use App\Actions\Container\UpdateContainer;
use App\Actions\HBL\UpdateHBLSystemStatus;
use App\Events\PackageUnloaded;
use App\Models\Container;
use App\Models\HBL;
use App\Models\HBLPackage;
use App\Models\Scopes\BranchScope;
use App\Services\UnloadingAuditService;
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
            $auditService = app(UnloadingAuditService::class);

            DB::beginTransaction();

            foreach ($data['packages'] as $packageData) {
                $container->hbl_packages()->updateExistingPivot($packageData['id'], [
                    'status' => 'draft-unload',
                    'unloaded_by' => auth()->id(),
                ]);

                $container->duplicate_hbl_packages()->updateExistingPivot($packageData['id'], [
                    'status' => 'draft-unload',
                    'unloaded_by' => auth()->id(),
                ]);

                $hbl = HBL::withoutGlobalScope(BranchScope::class)->find($packageData['hbl_id']);

                UpdateHBLSystemStatus::run($hbl, 4.3);

                // Get fresh package data with relationships for broadcasting
                $package = HBLPackage::withoutGlobalScope(BranchScope::class)
                    ->with(['hbl.mhbl', 'unloadingIssue', 'latestDetainRecord'])
                    ->find($packageData['id']);

                if ($package) {
                    // Log the unload action
                    $auditService->logPackageAction($container, $package, 'unload');

                    // Broadcast the unload event
                    $user = auth()->user();
                    $userName = !empty($user->name) ? $user->name : ($user->username ?? 'Unknown User');
                    broadcast(new PackageUnloaded(
                        $container->id,
                        $package->toArray(),
                        'unload',
                        auth()->id(),
                        $userName
                    ));
                }
            }

            // update container loading start datetime and who loaded by
            $updateData = [
                'unloading_started_at' => now(),
                'unloading_started_by' => auth()->id(),
            ];

            UpdateContainer::run($container, $updateData);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to create draft loaded container: '.$e->getMessage());
        }
    }
}
