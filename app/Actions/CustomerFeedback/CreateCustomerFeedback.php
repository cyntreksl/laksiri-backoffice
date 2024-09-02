<?php

namespace App\Actions\CustomerFeedback;

use App\Models\CustomerFeedback;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateCustomerFeedback
{
    use AsAction;

    public function handle(array $data): CustomerFeedback
    {

        $customerFeedback = CustomerFeedback::create([
            'token_id' => $data['tokenId'],
            'user_id' => $data['userId'],
            'hbl_id' => $data['hblId'],
            'rating' => $data['rating'],
            'feedback' => $data['feedback'],

        ]);

        return $customerFeedback;
    }
}
