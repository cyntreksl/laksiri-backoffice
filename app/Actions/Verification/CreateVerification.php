<?php

namespace App\Actions\Verification;

use App\Models\Verification;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateVerification
{
    use AsAction;

    public function handle(array $data)
    {
        Verification::create([
            'is_checked' => $data['is_checked'],
            'verified_by' => auth()->id(),
            'customer_queue_id' => $data['customer_queue']['id'],
            'token_id' => $data['customer_queue']['token_id'],
            'note' => $data['note'],
        ]);
    }
}
