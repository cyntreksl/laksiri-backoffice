<?php

namespace App\Exports;

use App\Actions\HBL\GetHBLByHBLNumber;
use App\Models\Container;
use App\Models\HBL;
use App\Models\Scopes\BranchScope;

class LoadedContainerTallySheetExport
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function query()
    {
        return Container::query()
            ->withoutGlobalScope(BranchScope::class)
            ->with(['hbl_packages' => function ($query) {
                $query->withoutGlobalScope(BranchScope::class);
            }])
            ->where('id', $this->container->id);
    }

    public function prepareData(): array
    {
        //        $groupedPackages = $this->container->load('duplicate_hbl_packages.hbl.mhbl')->duplicate_hbl_packages->groupBy(function ($package) {
        //            return optional($package->hbl->mhbl)->hbl_number ?? $package->hbl->hbl_number;
        //        });
        $data = [];

        // Get the currently loaded HBL package IDs
        $currentlyLoadedPackageIds = $this->container->hbl_packages->pluck('id')->toArray();

        // Filter duplicate_hbl_packages to only include those that are still in the container's hbl_packages
        $filteredPackages = $this->container->load('duplicate_hbl_packages.hbl.mhbl')->duplicate_hbl_packages->filter(function ($package) use ($currentlyLoadedPackageIds) {
            return in_array($package->id, $currentlyLoadedPackageIds);
        });

        $groupedPackages = $filteredPackages->groupBy(function ($package) {
            return $package->hbl->hbl_number;
        });
        foreach ($groupedPackages as $hblNumber => $hblPackages) {
            $hbl = GetHBLByHBLNumber::run($hblNumber);
            $data[] = [
                $hbl->hbl_number,
                $hbl->hbl_name,
                $hblPackages->sum('volume'),
                count($hblPackages),
                $hblPackages->pluck('package_type')->values()->all(),
                $hbl->warehouse === 'COLOMBO' ? 'CMB' : 'NTR',
                '',
            ];
        }

        return $data;
    }
}
