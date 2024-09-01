<?php

namespace App\Interfaces\CallCenter;

interface UserFeedbackRepositoryInterface
{
    public function createUserFeedback(array $data);
}
