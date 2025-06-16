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
            $data['total'] = $data['do_charge'] + $data['demurrage_charge'] + $data['assessment_charge'] + $data['refund_charge'] + $data['slpa_charge'] + $data['clearance_charge'];

            $existing = ContainerPayment::where('container_id', $data['container_id'])->first();

            if ($existing) {
                $existing->update([
                    'do_charge' => $data['do_charge'],
                    'demurrage_charge' => $data['demurrage_charge'],
                    'assessment_charge' => $data['assessment_charge'],
                    'slpa_charge' => $data['slpa_charge'],
                    'refund_charge' => $data['refund_charge'],
                    'clearance_charge' => $data['clearance_charge'],
                    'total' => $data['total'],
                ]);

                return $existing;
            } else {
                $data['created_by'] = Auth::user()->id;

                return ContainerPayment::create($data);
            }

        } catch (\Exception $e) {
            throw new \Exception('Failed to create or update container payment: '.$e->getMessage());
        }
    }
}
