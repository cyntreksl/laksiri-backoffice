<?php

namespace App\Actions\ThirdPartyShipment;

use App\Actions\Container\Loading\CreateOrUpdateLoadedContainer;
use App\Actions\HBL\GenerateCRNumber;
use App\Actions\HBL\GenerateHBLNumber;
use App\Actions\HBL\GenerateHBLReferenceNumber;
use App\Actions\User\GetUserCurrentBranchID;
use App\Enum\ContainerStatus;
use App\Enum\WarehouseType;
use App\Models\Container;
use App\Models\HBL;
use App\Models\HblPackage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Lorisleiva\Actions\Concerns\AsAction;

class SaveThirdPartyShipmentV2
{
    use AsAction;

    public function handle(array $data)
    {
        return DB::transaction(function () use ($data) {
            $allPackageIds = [];

            // Generate HBL reference
            $reference = GenerateHBLReferenceNumber::run();

            // Create actual HBL
            // For third-party HBLs, use the user-provided HBL number if available
            // Otherwise fall back to auto-generated reference
            $hblNumber = !empty($data['hbl']) ? $data['hbl'] : $reference;
            
            // Use selected third-party agent as the branch, or fall back to current branch
            $branchId = $data['agent'] ?? GetUserCurrentBranchID::run();

            $hbl = HBL::create([
                'reference' => $reference,
                'branch_id' => $branchId, // Use selected third-party agent
                'warehouse_id' => GetUserCurrentBranchID::run(),
                'cargo_type' => $data['cargo_type'],
                'hbl_type' => $data['hbl_type'],
                'hbl' => $hblNumber, // Use user-provided HBL number
                'hbl_name' => $data['hbl_name'],
                'email' => $data['email'],
                'contact_number' => $data['contact_number'],
                'additional_mobile_number' => $data['additional_mobile_number'],
                'whatsapp_number' => $data['whatsapp_number'],
                'nic' => $data['nic'],
                'iq_number' => $data['iq_number'],
                'address' => $data['address'],
                'consignee_name' => $data['consignee_name'],
                'consignee_nic' => $data['consignee_nic'],
                'consignee_contact' => $data['consignee_contact'],
                'consignee_additional_mobile_number' => $data['consignee_additional_mobile_number'],
                'consignee_whatsapp_number' => $data['consignee_whatsapp_number'],
                'consignee_address' => $data['consignee_address'],
                'consignee_note' => $data['consignee_note'],
                'warehouse' => WarehouseType::COLOMBO->value,
                'freight_charge' => $data['freight_charge'],
                'bill_charge' => $data['bill_charge'],
                'other_charge' => $data['other_charge'],
                'destination_charge' => $data['destination_charge'],
                'discount' => $data['discount'],
                'additional_charge' => $data['additional_charge'],
                'paid_amount' => $data['paid_amount'],
                'grand_total' => $data['grand_total'],
                'created_by' => auth()->id(),
                'pickup_id' => $data['pickup_id'] ?? null,
                'hbl_number' => $hblNumber, // Use same user-provided HBL number
                'cr_number' => GenerateCRNumber::run(),
                'system_status' => HBL::SYSTEM_STATUS_HBL_CREATED,
                'is_departure_charges_paid' => 1,
                'is_destination_charges_paid' => $data['is_destination_charges_paid'],
                'is_third_party' => true,
            ]);

            // Create HBL packages
            foreach ($data['packages'] as $tmpPackage) {
                Log::info('SaveThirdPartyShipmentV2: Processing package', [
                    'package_data' => $tmpPackage,
                    'volume_raw' => $tmpPackage['volume'] ?? 'MISSING',
                    'length' => $tmpPackage['length'] ?? 'MISSING',
                    'width' => $tmpPackage['width'] ?? 'MISSING',
                    'height' => $tmpPackage['height'] ?? 'MISSING',
                ]);

                $weight = !empty($tmpPackage['chargeableWeight']) ? $tmpPackage['chargeableWeight'] : ($tmpPackage['totalWeight'] ?? 0);
                
                // Calculate volumetric weight only if dimensions are present
                $volumetricWeight = 0;
                if (($tmpPackage['length'] ?? 0) > 0 && ($tmpPackage['width'] ?? 0) > 0 && ($tmpPackage['height'] ?? 0) > 0) {
                     $volumetricWeight = ($tmpPackage['length'] * $tmpPackage['width'] * $tmpPackage['height']) / 6000;
                }

                $package = HblPackage::create([
                    'branch_id' => $branchId, // Use same agent branch as HBL
                    'hbl_id' => $hbl->id,
                    'package_type' => $tmpPackage['type'],
                    'measure_type' => $tmpPackage['measure_type'],
                    'length' => $tmpPackage['length'],
                    'width' => $tmpPackage['width'],
                    'height' => $tmpPackage['height'],
                    'quantity' => $tmpPackage['quantity'],
                    'volume' => $tmpPackage['volume'],
                    'weight' => $weight,
                    'actual_weight' => $tmpPackage['totalWeight'] ?? 0,
                    'volumetric_weight' => $volumetricWeight,
                    'remarks' => $tmpPackage['remarks'],
                ]);
                $allPackageIds[] = ['id' => $package->id];
            }

            // Load all HBL packages into the selected container
            if (!empty($allPackageIds) && isset($data['shipment'])) {
                $container = Container::find($data['shipment']);
                if ($container) {
                    // Update to loaded status (this marks them as fully loaded)
                    CreateOrUpdateLoadedContainer::run([
                        'container_id' => $data['shipment'],
                        'packages' => $allPackageIds,
                        'note' => 'Third party shipment - Auto-loaded on HBL creation',
                    ]);

                    // Complete the loading process by recalculating weights
                    \App\Services\ContainerWeightService::recalculate($container);
                }
            }

            return $hbl;
        });
    }
}
