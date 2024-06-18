<?php

namespace App\Actions\Container\Unloading;

use App\Actions\HBL\HBLPackage\MarkAsUnloaded;
use App\Models\Container;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class UnloadHBLPackages
{
    use AsAction;

    /**
     * @throws \Exception
     */
    public function handle(Container $container)
    {
        try {
            DB::beginTransaction();

            $hblPackageIds = $container->hbl_packages()
                ->pluck('hbl_package_id');

            $container->hbl_packages()->detach($hblPackageIds);

            foreach ($hblPackageIds as $hblPackageId) {
                MarkAsUnloaded::run($hblPackageId);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to unload hbl packages from container: '.$e->getMessage());
        }
    }
}
