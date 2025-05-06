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
            $data['total'] = $data['do_charge'] + $data['demurrage_charge'] + $data['assessment_charge'] + $data['refund_charge'] + $data['clearance_charge'];
            $data['created_by'] = Auth::user()->id;

            return ContainerPayment::create($data);
        } catch (\Exception $e) {
            throw new \Exception('Failed to create container: '.$e->getMessage());
        }
    }
}
