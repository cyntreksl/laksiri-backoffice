<?php

namespace App\Exports;

use App\Actions\Branch\GetBranchByName;
use App\Actions\MHBL\GetMHBLById;
use App\Enum\WarehouseType;
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

        // Get the currently loaded HBL package IDs
        $currentlyLoadedPackageIds = $this->container->hbl_packages->pluck('id')->toArray();

        // Group packages by HBL, but only include packages that are still loaded in the container
        foreach ($this->container->duplicate_hbl_packages->groupBy('hbl_id') as $hblId => $packages) {
            // Filter packages to only include those that are still in the container's hbl_packages
            $stillLoadedPackages = $packages->filter(function ($package) use ($currentlyLoadedPackageIds) {
                return in_array($package->id, $currentlyLoadedPackageIds);
            });

            // Skip this HBL if all its packages have been removed from the container
            if ($stillLoadedPackages->isEmpty()) {
                continue;
            }

            $hbl = HBL::withoutGlobalScope(BranchScope::class)->with('mhbl')->find($hblId);
            if ($hbl->mhbl) {
                $loadedMHBLPackages[$hbl->mhbl->id][] = $stillLoadedPackages;
            } else {
                $loadedHBLPackages[$hblId] = [
                    'hbl' => $hbl,
                    'packages' => $stillLoadedPackages,
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

            $warehouse = null;

            if ($mhbl->hbls[0]->warehouse_id) {
                $warehouse = match ($mhbl->hbls[0]->warehouse_id) {
                    GetBranchByName::run(WarehouseType::COLOMBO->value)['id'] => 'CMB',
                    GetBranchByName::run(WarehouseType::NINTAVUR->value)['id'] => 'NTR',
                    default => null,
                };
            } elseif ($mhbl->hbls[0]->warehouse) {
                $warehouse = match (ucwords($mhbl->hbls[0]->warehouse)) {
                    WarehouseType::COLOMBO->value => 'CMB',
                    WarehouseType::NINTAVUR->value => 'NTR',
                    default => null,
                };
            }

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

            $warehouse = null;

            if ($hbl->warehouse_id) {
                $warehouse = match ($hbl->warehouse_id) {
                    GetBranchByName::run(WarehouseType::COLOMBO->value)['id'] => 'CMB',
                    GetBranchByName::run(WarehouseType::NINTAVUR->value)['id'] => 'NTR',
                    default => null,
                };
            } elseif ($hbl->warehouse) {
                $warehouse = match (ucwords($hbl->warehouse)) {
                    WarehouseType::COLOMBO->value => 'CMB',
                    WarehouseType::NINTAVUR->value => 'NTR',
                    default => null,
                };
            }

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
