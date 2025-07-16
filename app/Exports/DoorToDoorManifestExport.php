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
        $hblsById = [];
        $mhblsById = [];

        // Gather all HBLs for this container
        foreach ($this->container->hbl_packages->groupBy('hbl_id') as $hblId => $loadedHBLPackages) {
            $hbl = HBL::withoutGlobalScope(BranchScope::class)->with('mhbl')->find($hblId);
            if (! $hbl) {
                continue;
            }
            $hblsById[$hbl->id] = [
                'hbl' => $hbl,
                'packages' => $loadedHBLPackages,
            ];
            if ($hbl->mhbl) {
                $mhblsById[$hbl->mhbl->id][] = $hbl->id;
            }
        }

        $includedHBLIds = [];

        // 1. List all Door to Door HBLs (not in a valid MHBL, or MHBL not all Door to Door)
        foreach ($hblsById as $hblId => $info) {
            $hbl = $info['hbl'];
            $mhbl = $hbl->mhbl;
            if ($hbl->hbl_type === 'Door to Door') {
                // If in MHBL, only include here if MHBL is not all Door to Door
                if ($mhbl) {
                    $mhblHBLs = $mhbl->hbls()->withoutGlobalScope(BranchScope::class)->get();
                    $allD2D = $mhblHBLs->every(fn ($mhblHbl) => $mhblHbl->hbl_type === 'Door to Door');
                    if ($allD2D) {
                        // Will be included in MHBL section
                        continue;
                    }
                }
                // Not in MHBL, or MHBL not all Door to Door
                foreach ($info['packages'] as $hbl_package) {
                    $data[] = $this->formatHBLRow($hbl, $hbl_package, null, $containerId);
                }
                $includedHBLIds[] = $hbl->id;
            }
        }

        // 2. For each MHBL, if all HBLs are Door to Door, list all its HBLs (avoid duplicates)
        foreach ($mhblsById as $mhblId => $hblIds) {
            $mhbl = null;
            $mhblHBLs = [];
            foreach ($hblIds as $hblId) {
                $hbl = $hblsById[$hblId]['hbl'];
                if (! $mhbl) {
                    $mhbl = $hbl->mhbl;
                }
                $mhblHBLs[] = $hbl;
            }
            if ($mhbl) {
                $allD2D = $mhbl->hbls()->withoutGlobalScope(BranchScope::class)->get()->every(fn ($mhblHbl) => $mhblHbl->hbl_type === 'Door to Door');
                if ($allD2D) {
                    foreach ($mhblHBLs as $hbl) {
                        if (in_array($hbl->id, $includedHBLIds)) {
                            continue;
                        } // skip if already included
                        foreach ($hblsById[$hbl->id]['packages'] as $hbl_package) {
                            $data[] = $this->formatHBLRow($hbl, $hbl_package, $mhbl, $containerId);
                        }
                        $includedHBLIds[] = $hbl->id;
                    }
                }
            }
        }

        // Remove duplicates by HBL number/reference
        $uniqueData = [];
        $seen = [];
        foreach ($data as $item) {
            $key = $item[0]; // hbl_number or reference
            if (! in_array($key, $seen)) {
                $seen[] = $key;
                $uniqueData[] = $item;
            }
        }

        return $uniqueData;
    }

    // Helper to format a row for export
    private function formatHBLRow($hbl, $hbl_package, $mhbl, $containerId)
    {
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

        return [
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
            $mhbl, // This will be null or the MHBL object
            $containerId,
            ($hbl->branch['currency_symbol'].' '.$hbl['grand_total']) ?? '',
        ];
    }
}
