<?php

namespace App\Exports;

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

        foreach ($this->container->hbl_packages->groupBy('hbl_id') as $hblId => $loadedHBLPackages) {
            $hbl = HBL::withoutGlobalScope(BranchScope::class)->find($hblId);
            if (! $hbl) {
                continue;
            } // Skip if HBL is missing

            $isFirst = true;
            $totalQuantity = $loadedHBLPackages->sum('quantity');

            foreach ($loadedHBLPackages as $hbl_package) {
                $data[] = [
                    $isFirst ? $hbl->hbl_number ?: $hbl->reference : '',
                    $isFirst ? $hbl->hbl_name : '',
                    $isFirst ? $hbl->consignee_name : '',
                    $isFirst ? $hbl->consignee_address : '',
                    $isFirst ? $hbl->consignee_nic : '',
                    $isFirst ? $hbl->consignee_contact : '',
                    $hbl_package->package_type,
                    $isFirst ? $totalQuantity : '',
                    $hbl_package->volume,
                    $hbl_package->weight,

                ];

                $isFirst = false;
            }

            // Add additional rows (address, NIC, etc.)
            $data[] = ['', $hbl->address, $hbl->consignee_address, '', '', '', ''];
            $data[] = ['', $hbl->nic, $hbl->consignee_nic, '', '', '', ''];
            $data[] = ['', $hbl->contact_number, $hbl->consignee_contact, '', '', '', ''];
        }

        return $data;
    }
}
