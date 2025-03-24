<?php

namespace App\Actions\HBL;

use App\Models\HBL;
use App\Models\HBLPackage;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class UpdateHBLPackages
{
    use AsAction;

    public function handle(HBL $hbl, array $data)
    {
        DB::transaction(function () use ($hbl, $data) {
            $newPackages = [];

            $existPackages = array_filter($data, function ($item) {
                return isset($item['id']);
            });

            $newPackages = array_filter($data, function ($item) {
                return ! isset($item['id']);
            });

            $existPackageIds = collect($existPackages)->pluck('id')->toArray();

            $deletedPackages = $hbl->packages->reject(function ($package) use ($existPackageIds) {
                return in_array($package->id, $existPackageIds);
            });
            foreach ($deletedPackages as $packageToDelete) {
                $packageToDelete->delete();
            $existPackages = array_filter($data, function ($item) {
                return isset($item['id']);
            });
            $newPackages = array_filter($data, function ($item) {
                return ! isset($item['id']);
            });
            $existPackageIds = collect($existPackages)->pluck('id')->toArray();
            $deletedPackages = $hbl->packages->reject(function ($package) use ($existPackageIds) {
                return in_array($package->id, $existPackageIds);
            });
            foreach ($deletedPackages as $packageToDelete) {
                $packageToDelete->delete();
            }
            CreateHBLPackages::run($hbl, $newPackages);
        });
    }
}
