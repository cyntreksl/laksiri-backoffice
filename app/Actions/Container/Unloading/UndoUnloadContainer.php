<?php

namespace App\Actions\Container\Unloading;

use App\Models\Container;
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

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to undo draft unloading: '.$e->getMessage());
        }
    }
}
