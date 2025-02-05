<?php

namespace App\Http\Controllers;

use App\Interfaces\CallCenter\UserFeedbackRepositoryInterface;
use App\Mail\GetFeedback;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;

class UserFeedbackController extends Controller
{
    public function __construct(
        private readonly UserFeedbackRepositoryInterface $UserFeedbackRepository,
    ) {}

    public function viewFeedbackForm()
    {
        return Inertia::render('CallCenter/CustomerFeedback/FeedbackForm');
    }

    public function storeUserFeedback(Request $request)
    {
        $data = [
            'tokenId' => $request->tokenId,
            'userId' => $request->userId,
            'hblId' => $request->hblId,
            'rating' => $request->rating,
            'feedback' => $request->comment,
        ];
        $this->UserFeedbackRepository->createUserFeedback($data);
    }

    public function testSendEmail($userId, $hblId, $token)
    {
        $feedbackURL = 'http://127.0.0.1:8000/your-feedback?user='.$userId.'&hbl='.$hblId.'&token='.$token;
        Mail::to('imalshaweerakkodi@gmail.com')->send(new GetFeedback($feedbackURL));
    }
}
