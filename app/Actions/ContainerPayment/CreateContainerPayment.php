<?php

namespace App\Actions\ContainerPayment;

use App\Models\ContainerPayment;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateContainerPayment
{
    use AsAction;

    /**
     * @throws \Exception
     */
    public function handle(array $data): ContainerPayment
    {
        try {
            $existing = ContainerPayment::where('container_id', $data['container_id'])->first();

            if ($existing) {
                $updateData = [];
                $chargeTypes = [
                    'do_charge',
                    'demurrage_charge',
                    'assessment_charge',
                    'refund_charge',
                    'slpa_charge',
                    'clearance_charge',
                ];

                // Only update the charges that are provided in the data
                foreach ($chargeTypes as $chargeType) {
                    if (isset($data[$chargeType])) {
                        $updateData[$chargeType] = $data[$chargeType];
                        // Set the requested timestamp for this charge
                        $updateData["{$chargeType}_requested_at"] = now();
                        $updateData["{$chargeType}_requested_by"] = Auth::user()->id;
                    }
                }

                // Recalculate total only when updating
                if (! empty($updateData)) {
                    $existing->fill($updateData);
                    $existing->total = $existing->do_charge +
                        $existing->demurrage_charge +
                        $existing->assessment_charge +
                        $existing->refund_charge +
                        $existing->slpa_charge +
                        $existing->clearance_charge;
                    $existing->save();
                }

                return $existing;
            } else {
                $data['created_by'] = Auth::user()->id;
                // Set the requested timestamp for the provided charge
                foreach ($data as $key => $value) {
                    if (strpos($key, '_charge') !== false && $key !== 'total') {
                        $data["{$key}_requested_at"] = now();
                        $data["{$key}_requested_by"] = Auth::user()->id;
                    }
                }

                // Calculate total for new record
                $data['total'] = ($data['do_charge'] ?? 0) +
                    ($data['demurrage_charge'] ?? 0) +
                    ($data['assessment_charge'] ?? 0) +
                    ($data['refund_charge'] ?? 0) +
                    ($data['slpa_charge'] ?? 0) +
                    ($data['clearance_charge'] ?? 0);

                return ContainerPayment::create($data);
            }

        } catch (\Exception $e) {
            throw new \Exception('Failed to create or update container payment: '.$e->getMessage());
        }
    }
}
