<?php

namespace App\Actions\CustomerFeedback;

use App\Models\User;
use Lorisleiva\Actions\Concerns\AsAction;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schedule;

use App\Mail\GetFeedback;

class SendFeedbackMail
{
    use AsAction;

    public function handle(array $data)
    {
        $feedbackURL = 'http://127.0.0.1:8000/your-feedback?user=' . $data['customerId'] . '&hbl=' . $data['hblId'] . '&token=' . $data['tokenId'];
        $customer = User::find($data['customerId']);
        Mail::to($customer->email)->later(
            now()->addMinutes(30),
            new GetFeedback($feedbackURL)
        );
    }
}
