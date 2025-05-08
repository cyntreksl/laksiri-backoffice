<?php

namespace App\Interfaces;

use App\Models\Container;
use App\Models\ContainerPayment;

interface ContainerPaymentRepositoryInterface
{
    public function getContainerPayment(Container $container);

    public function store(array $data): ContainerPayment;

    public function delete(ContainerPayment $containerPayment);

    public function markRefundCollection(array $containerPaymentIds);
}
