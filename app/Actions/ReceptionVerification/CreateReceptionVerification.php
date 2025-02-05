<?php

namespace App\Actions\ReceptionVerification;

use App\Models\ReceptionVerification;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateReceptionVerification
{
    use AsAction;

    public function handle(array $data)
    {
        ReceptionVerification::create([
            'is_checked' => $data['is_checked'],
            'verified_by' => auth()->id(),
            'customer_queue_id' => $data['customer_queue']['id'],
            'token_id' => $data['customer_queue']['token_id'],
            'note' => $data['note'],
        ]);
    }
}
