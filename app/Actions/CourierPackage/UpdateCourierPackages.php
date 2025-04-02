<?php

namespace App\Actions\CourierPackage;

use App\Actions\Courier\CreateCourierPackages;
use App\Models\Courier;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateCourierPackages
{
    use AsAction;

    public function handle(Courier $courier, array $data)
    {
        DB::transaction(function () use ($courier, $data) {
            $newPackages = [];

            $existPackages = array_filter($data, function ($item) {
                return isset($item['id']);
            });

            $newPackages = array_filter($data, function ($item) {
                return ! isset($item['id']);
            });

            $existPackageIds = collect($existPackages)->pluck('id')->toArray();

            $deletedPackages = $courier->packages->reject(function ($package) use ($existPackageIds) {
                return in_array($package->id, $existPackageIds);
            });
            foreach ($deletedPackages as $packageToDelete) {
                $packageToDelete->delete();
            }
            CreateCourierPackages::run($courier, $newPackages);
        });
    }
}
