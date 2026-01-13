<?php

namespace App\Actions\Container\Unloading;

use App\Actions\Container\UpdateContainer;
use App\Actions\HBL\UpdateHBLSystemStatus;
use App\Events\PackageUnloaded;
use App\Models\Container;
use App\Models\HBLPackage;
use App\Models\MHBL;
use App\Models\Scopes\BranchScope;
use App\Services\UnloadingAuditService;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class UnloadMHBLGroup
{
    use AsAction;

    /**
     * Unload all packages from an MHBL at once
     * 
     * @throws \Exception
     */
    public function handle(array $data)
    {
        try {
            $container = Container::withoutGlobalScope(BranchScope::class)->find($data['container_id']);
            $mhbl = MHBL::withoutGlobalScope(BranchScope::class)->find($data['mhbl_id']);
            $auditService = app(UnloadingAuditService::class);

            DB::beginTransaction();

            // Get all packages for this MHBL in the container
            $packages = HBLPackage::withoutGlobalScope(BranchScope::class)
                ->with(['hbl.mhbl', 'unloadingIssue', 'latestDetainRecord'])
                ->whereIn('id', $data['package_ids'])
                ->get();

            foreach ($packages as $package) {
                $container->hbl_packages()->updateExistingPivot($package->id, [
                    'status' => 'draft-unload',
                    'unloaded_by' => auth()->id(),
                ]);

                $container->duplicate_hbl_packages()->updateExistingPivot($package->id, [
                    'status' => 'draft-unload',
                    'unloaded_by' => auth()->id(),
                ]);

                // Update HBL status
                if ($package->hbl) {
                    UpdateHBLSystemStatus::run($package->hbl, 4.3);
                }

                // Broadcast individual package unload event
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

            // Log the MHBL-level unload action
            $auditService->logMHBLAction($container, $mhbl, $packages->toArray(), 'unload');

            // Update container unloading start datetime
            $updateData = [
                'unloading_started_at' => now(),
                'unloading_started_by' => auth()->id(),
            ];

            UpdateContainer::run($container, $updateData);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to unload MHBL group: '.$e->getMessage());
        }
    }
}
