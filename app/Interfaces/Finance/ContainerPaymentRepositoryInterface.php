<?php

namespace App\Interfaces\Finance;

interface ContainerPaymentRepositoryInterface
{
    public function approveContainerPayments(array $containerPaymentIds);

    public function revokeContainerPaymentsApprovals(array $containerPaymentIds);

    public function completePayments(array $data);
}
