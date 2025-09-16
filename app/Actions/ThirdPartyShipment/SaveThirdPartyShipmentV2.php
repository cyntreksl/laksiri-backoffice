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
            $hbl = HBL::create([
                'reference' => $reference,
                'branch_id' => GetUserCurrentBranchID::run(),
                'warehouse_id' => GetUserCurrentBranchID::run(),
                'cargo_type' => $data['cargo_type'],
                'hbl_type' => $data['hbl_type'],
                'hbl' => $reference,
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
                'hbl_number' => GenerateHBLNumber::run(GetUserCurrentBranchID::run()),
                'cr_number' => GenerateCRNumber::run(),
                'system_status' => HBL::SYSTEM_STATUS_HBL_CREATED,
                'is_departure_charges_paid' => 1,
                'is_destination_charges_paid' => $data['is_destination_charges_paid'],
                'is_third_party' => true,
            ]);

            // Create HBL packages
            foreach ($data['packages'] as $tmpPackage) {
                $weight = $tmpPackage['chargeableWeight'] ?? $tmpPackage['totalWeight'] ?? 0;

                $package = HblPackage::create([
                    'branch_id' => GetUserCurrentBranchID::run(),
                    'hbl_id' => $hbl->id,
                    'package_type' => $tmpPackage['type'],
                    'measure_type' => $tmpPackage['measure_type'],
                    'length' => $tmpPackage['length'],
                    'width' => $tmpPackage['width'],
                    'height' => $tmpPackage['height'],
                    'quantity' => $tmpPackage['quantity'],
                    'volume' => $tmpPackage['volume'],
                    'weight' => $weight,
                    'actual_weight' => $weight,
                    'volumetric_weight' => ($tmpPackage['length'] * $tmpPackage['width'] * $tmpPackage['height']) / 6000,
                    'remarks' => $tmpPackage['remarks'],
                ]);
                $allPackageIds[] = ['id' => $package->id];
            }

            // Load all HBL packages into the selected container
            if (! empty($allPackageIds) && isset($data['shipment'])) {
                $container = Container::find($data['shipment']);
                if ($container) {
                    CreateOrUpdateLoadedContainer::run([
                        'container_id' => $data['shipment'],
                        'packages' => $allPackageIds,
                        'note' => 'Third party shipment - Manual Create Option',
                        'status' => ContainerStatus::IN_TRANSIT->value,
                    ]);
                }
            }
        });
    }
}
