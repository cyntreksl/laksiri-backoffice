<?php

namespace App\Interfaces\Finance;

interface ContainerPaymentRepositoryInterface
{
    public function approveContainerPayments(array $containerPaymentIds);
}
