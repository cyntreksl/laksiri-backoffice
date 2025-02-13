<?php

namespace App\Actions\CustomerFeedback;

use App\Mail\GetFeedback;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Lorisleiva\Actions\Concerns\AsAction;

class SendFeedbackMail
{
    use AsAction;

    public function handle(array $data)
    {
        $feedbackURL = 'https://laksiri.world/your-feedback?user='.$data['customerId'].'&hbl='.$data['hblId'].'&token='.$data['tokenId'];
        $customer = User::find($data['customerId']);
        Mail::to($customer->email)->later(
            now()->addMinutes(30),
            new GetFeedback($feedbackURL)
        );
    }
}
