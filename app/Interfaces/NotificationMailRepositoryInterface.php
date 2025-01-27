<?php

namespace App\Interfaces;

interface NotificationMailRepositoryInterface
{
    public function sendAssignDriverNotification(array $data);
}
