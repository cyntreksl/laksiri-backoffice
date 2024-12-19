<?php

namespace App\Exports;

use App\Models\Container;
use App\Models\HBL;
use App\Models\Scopes\BranchScope;

class DoorToDoorManifestExport
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
        $data = [];

        foreach ($this->container->hbl_packages->groupBy('hbl_id') as $hblId => $loadedHBLPackages) {
            $hbl = HBL::withoutGlobalScope(BranchScope::class)->find($hblId);
            if (!$hbl) {
                continue;
            } // Skip if HBL is missing

            $isFirst = true;
            $totalQuantity = $loadedHBLPackages->sum('quantity');
            //            dd($loadedHBLPackages);

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

        }
        return $data;
    }
}
