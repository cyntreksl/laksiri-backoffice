<?php

namespace App\Repositories\CallCenter;

use App\Actions\CustomerFeedback\CreateCustomerFeedback;
use App\Interfaces\CallCenter\UserFeedbackRepositoryInterface;

class UserFeedbackRepository implements UserFeedbackRepositoryInterface
{
    public function createUserFeedback(array $data)
    {
        return CreateCustomerFeedback::run($data);
    }
}
