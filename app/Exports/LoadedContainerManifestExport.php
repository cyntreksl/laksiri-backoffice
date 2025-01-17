<?php

namespace App\Exports;

use App\Actions\MHBL\GetMHBLById;
use App\Models\Container;
use App\Models\HBL;
use App\Models\Scopes\BranchScope;

class LoadedContainerManifestExport
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    //    public function headings(): array
    //    {
    //        return [
    //            'HBL',
    //            'Shipper Details',
    //            'Consignee Details',
    //            'Type',
    //            'Quantity',
    //            'Volume',
    //            'Weight',
    //        ];
    //    }

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
        $data = [];

        $loadedHBLPackages = [];
        $loadedMHBLPackages = [];

        foreach ($this->container->hbl_packages->groupBy('hbl_id') as $hblId => $loadedHBLPackages) {
            $hbl = HBL::withoutGlobalScope(BranchScope::class)->with('mhbl')->find($hblId);
            if ($hbl->mhbl) {
                $loadedMHBLPackages[$hbl->mhbl->id][] = $loadedHBLPackages;
            } else {
                $loadedHBLPackages[] = $loadedHBLPackages;
            }
        }

        $isFirst = true;

        foreach ($loadedMHBLPackages as $mhblId => $mhblPackage) {
            $mhbl = GetMHBLById::run($mhblId);
            $hblPackages = [];
            if (! empty($mhbl->hbls)) {
                foreach ($mhbl->hbls as $mhblHBL) {
                    foreach ($mhblHBL->packages as $hblPackage) {
                        $hblPackages[] = $hblPackage;
                    }
                }
            }
            $data[] = [
                $mhbl->hbl_number ? $mhbl->reference : '',
                $mhbl->shipper->name ?? '',
                $mhbl->shipper->address ?? '',
                $mhbl->shipper->pp_or_nic_no ?? '',
                $mhbl->shipper->mobile_number ?? '',
                $mhbl->consignee->name ?? '',
                $mhbl->consignee->address ?? '',
                $mhbl->consignee->pp_or_nic_no ?? '',
                $mhbl->consignee->mobile_number ?? '',
                $hblPackages ?? [],
                $mhbl->hbls[0]->paid_amount > 0 ? 'PAID' : 'UNPAID',
            ];
        }
        foreach ($loadedHBLPackages as $hbl_package) {
            $data[] = [
                $isFirst ? $hbl->hbl_number ?: $hbl->reference : '',
                $isFirst ? $hbl->hbl_name : '',
                $isFirst ? $hbl->address : '',
                $isFirst ? $hbl->nic : '',
                $isFirst ? $hbl->contact_number : '',
                $isFirst ? $hbl->consignee_name : '',
                $isFirst ? $hbl->consignee_address : '',
                $isFirst ? $hbl->consignee_nic : '',
                $isFirst ? $hbl->consignee_contact : '',
                $isFirst ? $hbl->packages : [],
                $isFirst && $hbl->paid_amount > 0 ? 'PAID' : 'UNPAID',
            ];

            $isFirst = false;
        }

        return $data;
    }
}
