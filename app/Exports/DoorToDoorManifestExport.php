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
        $containerId = $this->container->id;

        foreach ($this->container->hbl_packages->groupBy('hbl_id') as $hblId => $loadedHBLPackages) {
            $hbl = HBL::withoutGlobalScope(BranchScope::class)->with('mhbl')->find($hblId);
            if (! $hbl) {
                continue;
            } // Skip if HBL is missing

            $mhbl = $hbl->mhbl; // Access mhbl relationship
            if (! $mhbl) {
                continue; // Skip if MHBL is missing
            }
            $isFirst = true;
            $totalQuantity = $loadedHBLPackages->sum('quantity');
            $warehouse = $hbl->warehouse_id
                ? ($hbl->warehouse_id === 2 ? 'CMB' : ($hbl->warehouse_id === 3 ? 'NTR' : null))
                : ($hbl->warehouse
                    ? ($hbl->warehouse === 'COLOMBO' ? 'CMB' : ($hbl->warehouse === 'NINTAVUR' ? 'NTR' : null))
                    : null);
            $isHBLFullLoad = $hbl->packages->every(fn ($package) => $package->duplicate_containers->isNotEmpty());
            $hblLoadedContainers = $hbl->packages
                ->load('duplicate_containers')
                ->pluck('duplicate_containers')
                ->flatten()
                ->unique('id')
                ->sortByDesc('created_at');
            $hblLoadedLatestContainer = $hbl->packages
                ->load('duplicate_containers')
                ->pluck('duplicate_containers')
                ->flatten()
                ->unique('id')
                ->sortByDesc('created_at')
                ->first();
            if ($isHBLFullLoad && count($hblLoadedContainers) === 1) {
                $status = '';
            } elseif (count($hblLoadedContainers) > 1 && $hblLoadedLatestContainer['id'] === $this->container['id']) {
                $status = 'BALANCE';
            } else {
                $status = 'SHORT LOADED';
            }
            $loadedContainerReferences = $hbl->packages->load('duplicate_containers')
                ->pluck('duplicate_containers')
                ->flatten()
                ->pluck('reference')
                ->unique();
            $filteredReferences = $loadedContainerReferences->reject(function ($ref) {
                return $ref == $this->container['reference'];
            });
            $referencesString = $filteredReferences->implode(',');

            foreach ($loadedHBLPackages as $hbl_package) {
                $data[] = [
                    $hbl->hbl_number ?: $hbl->reference,
                    $hbl->hbl_name,
                    $hbl->address,
                    $hbl->nic,
                    $hbl->contact_number,
                    $hbl->consignee_name,
                    $hbl->consignee_address,
                    $hbl->consignee_nic,
                    $hbl->consignee_contact.($hbl->consignee_additional_mobile_number ? '/'.$hbl->consignee_additional_mobile_number : ''),
                    $hbl->packages,
                    $hbl->paid_amount > 0 ? 'PAID' : 'UNPAID',
                    $hbl->hbl_type,
                    $hbl->other_charge,
                    $warehouse,
                    $hbl->iq_number,
                    $hbl->is_departure_charges_paid,
                    $hbl->is_destination_charges_paid,
                    $status,
                    $referencesString ? "SHIP NO. $referencesString" : null,
                    $hbl->branch['currency_symbol'].' '.$hbl['grand_total'],
                    $mhbl,
                    $containerId,
                    ($hbl->branch['currency_symbol'].' '.$hbl['grand_total']) ?? '',
                ];

                $isFirst = false;
            }

        }
        $uniqueData = [];
        $seen = [];
        foreach ($data as $item) {
            if (! in_array($item[0], $seen)) {
                $seen[] = $item[0];
                $uniqueData[] = $item;
            }
        }

        return $uniqueData;
    }
}
