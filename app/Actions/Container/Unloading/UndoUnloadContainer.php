<?php

namespace App\Actions\Container\Unloading;

use App\Events\PackageUnloaded;
use App\Models\Container;
use App\Models\HBLPackage;
use App\Models\Scopes\BranchScope;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class UndoUnloadContainer
{
    use AsAction;

    /**
     * @throws \Exception
     */
    public function handle(array $data)
    {
        try {
            $container = Container::withoutGlobalScope(BranchScope::class)
                ->find($data['container_id']);

            DB::beginTransaction();

            $container->hbl_packages()->updateExistingPivot($data['package_id'], [
                'status' => 'loaded',
                'unloaded_by' => null,
            ]);

            $container->duplicate_hbl_packages()->updateExistingPivot($data['package_id'], [
                'status' => 'loaded',
                'unloaded_by' => null,
            ]);

            // Get fresh package data with relationships for broadcasting
            $package = HBLPackage::withoutGlobalScope(BranchScope::class)
                ->with(['hbl.mhbl', 'unloadingIssue', 'latestDetainRecord'])
                ->find($data['package_id']);

            if ($package) {
                // Broadcast the reload event
                $user = auth()->user();
                $userName = !empty($user->name) ? $user->name : ($user->username ?? 'Unknown User');
                broadcast(new PackageUnloaded(
                    $container->id,
                    $package->toArray(),
                    'reload',
                    auth()->id(),
                    $userName
                ));
            }

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to undo draft unloading: '.$e->getMessage());
        }
    }
}
