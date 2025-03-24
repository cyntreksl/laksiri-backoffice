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
        $loadedMHBLPackages = [];
        $loadedHBLPackages = [];

        // Group packages by HBL
        foreach ($this->container->duplicate_hbl_packages->groupBy('hbl_id') as $hblId => $packages) {
            $hbl = HBL::withoutGlobalScope(BranchScope::class)->with('mhbl')->find($hblId);
            if ($hbl->mhbl) {
                $loadedMHBLPackages[$hbl->mhbl->id][] = $packages;
            } else {
                $loadedHBLPackages[$hblId] = [
                    'hbl' => $hbl,
                    'packages' => $packages,
                ];
            }
        }

        //  MHBL packages
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
            $warehouse = $mhbl->hbls[0]->warehouse_id
                ? ($mhbl->hbls[0]->warehouse_id === 2 ? 'CMB' : ($mhbl->hbls[0]->warehouse_id === 3 ? 'NTR' : null))
                : ($mhbl->hbls[0]->warehouse
                    ? ($mhbl->hbls[0]->warehouse === 'COLOMBO' ? 'CMB' : ($mhbl->hbls[0]->warehouse === 'NINTAVUR' ? 'NTR' : null))
                    : null);
            $data[] = [
                $mhbl->hbl_number ?: $mhbl->reference,
                $mhbl->shipper->name ?? '',
                $mhbl->shipper->address ?? '',
                $mhbl->shipper->pp_or_nic_no ?? '',
                $mhbl->shipper->mobile_number ?? '',
                $mhbl->consignee->name ?? '',
                $mhbl->consignee->address ?? '',
                $mhbl->consignee->pp_or_nic_no ?? '',
                $mhbl->consignee->mobile_number ?? '',
                collect($hblPackages ?? []),
                $mhbl->hbls[0]->paid_amount > 0 ? 'PAID' : 'UNPAID',
                'Gift',
                '',
                $warehouse,
                '',
                1,
                0,
                null,
                null,
                null,
            ];
        }

        // HBL packages
        foreach ($loadedHBLPackages as $hblData) {
            $hbl = $hblData['hbl'];
            $warehouse = $hbl->warehouse_id
                ? ($hbl->warehouse_id === 2 ? 'CMB' : ($hbl->warehouse_id === 3 ? 'NTR' : null))
                : ($hbl->warehouse
                    ? ($hbl->warehouse === 'COLOMBO' ? 'CMB' : ($hbl->warehouse === 'NINTAVUR' ? 'NTR' : null))
                    : null);

            $isHBLFullLoad = $hbl->packages->every(fn ($package) => $package->duplicate_containers->isNotEmpty());
            $hblLoadedLatestContainer = $hbl->packages
                ->load('duplicate_containers')
                ->pluck('duplicate_containers')
                ->flatten()
                ->unique('id')
                ->sortByDesc('created_at')
                ->first();
            $status = $hblLoadedLatestContainer['id'] === $this->container['id'] && $isHBLFullLoad ? 'BALANCE' : 'SHORT LOADED';
            $loadedContainerReferences = $hbl->packages->load('duplicate_containers')
                ->pluck('duplicate_containers')
                ->flatten()
                ->pluck('reference')
                ->unique();
            $filteredReferences = $loadedContainerReferences->reject(function ($ref) {
                return $ref == $this->container['reference'];
            });
            $referencesString = $filteredReferences->implode(',');

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
                $loadedHBLPackages[$hbl->id]['packages'],
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
            ];
        }

        return $data;
    }
}
