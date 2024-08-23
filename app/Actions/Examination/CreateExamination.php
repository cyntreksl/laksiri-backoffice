<?php

namespace App\Actions\Examination;

use App\Models\Examination;
use App\Models\HBL;
use App\Models\PackageQueue;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateExamination
{
    use AsAction;

    public function handle(array $data)
    {
        $hbl = HBL::where('reference', $data['customer_queue']['token']['reference'])->first();

        $package_queue = PackageQueue::where('token_id', $data['customer_queue']['token_id'])
            ->where('is_released', true)
            ->first();

        Examination::create([
            'released_by' => auth()->id(),
            'customer_queue_id' => $data['customer_queue']['id'],
            'token_id' => $data['customer_queue']['token_id'],
            'hbl_id' => $hbl->id,
            'package_queue_id' => $package_queue->id,
            'released_packages' => $data['released_packages'],
            'released_at' => now(),
            'note' => $data['note'],
        ]);
    }
}
