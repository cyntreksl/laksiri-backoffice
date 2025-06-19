<?php

namespace App\Actions\ThirdPartyShipment;

use App\Actions\Container\Loading\CreateOrUpdateLoadedContainer;
use App\Actions\HBL\GenerateHBLReferenceNumber;
use App\Actions\User\GetUserCurrentBranchID;
use App\Models\Container;
use App\Models\HBL;
use App\Models\HblPackage;
use App\Models\TmpHbl;
use App\Models\TmpHblPackage;
use Illuminate\Support\Facades\DB;
use Lorisleiva\Actions\Concerns\AsAction;

class SaveThirdPartyShipment
{
    use AsAction;

    public function handle(string $sessionId, array $requestData): array
    {
        return DB::transaction(function () use ($sessionId, $requestData) {
            $tmpHbls = TmpHbl::where('session_id', $sessionId)->with('packages')->get();

            if ($tmpHbls->isEmpty()) {
                throw new \Exception('No temporary HBL data found for this session.');
            }

            $savedHbls = [];
            $allPackageIds = [];

            foreach ($tmpHbls as $tmpHbl) {
                // Generate HBL reference
                $reference = GenerateHBLReferenceNumber::run();

                // Create actual HBL
                $hbl = HBL::create([
                    'reference' => $reference,
                    'branch_id' => GetUserCurrentBranchID::run(),
                    'warehouse_id' => GetUserCurrentBranchID::run(), // Set warehouse_id to current branch
                    'warehouse' => 'COLOMBO', // Hardcode warehouse as COLOMBO
                    'cargo_type' => $requestData['cargo_type'],
                    'hbl_type' => $requestData['hbl_type'],
                    'hbl' => $reference,
                    'hbl_number' => $tmpHbl->hbl_number,
                    'hbl_name' => $tmpHbl->hbl_name,
                    'email' => $tmpHbl->email,
                    'contact_number' => $tmpHbl->contact_number,
                    'additional_mobile_number' => $tmpHbl->additional_mobile_number,
                    'whatsapp_number' => $tmpHbl->whatsapp_number,
                    'nic' => $tmpHbl->nic,
                    'iq_number' => $tmpHbl->iq_number,
                    'address' => $tmpHbl->address,
                    'consignee_name' => $tmpHbl->consignee_name,
                    'consignee_nic' => $tmpHbl->consignee_nic,
                    'consignee_contact' => $tmpHbl->consignee_contact,
                    'consignee_additional_mobile_number' => $tmpHbl->consignee_additional_mobile_number,
                    'consignee_whatsapp_number' => $tmpHbl->consignee_whatsapp_number,
                    'consignee_address' => $tmpHbl->consignee_address,
                    'consignee_note' => $tmpHbl->consignee_note,
                    'system_status' => 3, // Default system status
                    'created_by' => auth()->id(),
                    'freight_charge' => 0,
                    'bill_charge' => 0,
                    'other_charge' => 0,
                    'destination_charge' => 0,
                    'discount' => 0,
                    'additional_charge' => 0,
                    'paid_amount' => 0,
                    'grand_total' => 0,
                    'do_charge' => 0,
                    'is_departure_charges_paid' => 1, // Set departure charges as paid
                ]);

                // Create HBL packages
                foreach ($tmpHbl->packages as $tmpPackage) {
                    $package = HblPackage::create([
                        'branch_id' => GetUserCurrentBranchID::run(),
                        'hbl_id' => $hbl->id,
                        'package_type' => $tmpPackage->package_type,
                        'measure_type' => $tmpPackage->measure_type,
                        'length' => $tmpPackage->length,
                        'width' => $tmpPackage->width,
                        'height' => $tmpPackage->height,
                        'quantity' => $tmpPackage->quantity,
                        'volume' => $tmpPackage->volume,
                        'weight' => $tmpPackage->weight,
                        'actual_weight' => $tmpPackage->weight,
                        'volumetric_weight' => ($tmpPackage->length * $tmpPackage->width * $tmpPackage->height) / 6000, // Standard calculation
                        'remarks' => $tmpPackage->remarks,
                    ]);

                    // Collect package IDs for container loading
                    $allPackageIds[] = ['id' => $package->id];
                }

                $savedHbls[] = $hbl;
            }

            // Load all HBL packages into the selected container
            if (! empty($allPackageIds) && isset($requestData['shipment'])) {
                $container = Container::find($requestData['shipment']);
                if ($container) {
                    CreateOrUpdateLoadedContainer::run([
                        'container_id' => $requestData['shipment'],
                        'packages' => $allPackageIds,
                        'note' => 'Third party shipment - Auto loaded via CSV import',
                    ]);
                }
            }

            // Clean up temporary data
            TmpHblPackage::where('session_id', $sessionId)->delete();
            TmpHbl::where('session_id', $sessionId)->delete();

            return [
                'success' => true,
                'message' => 'Third party shipment saved successfully',
                'hbls_created' => count($savedHbls),
                'hbl_ids' => collect($savedHbls)->pluck('id')->toArray(),
            ];
        });
    }
}
