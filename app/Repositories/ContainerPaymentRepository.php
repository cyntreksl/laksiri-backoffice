<?php

namespace App\Repositories;

use App\Actions\Container\GetContainerPayment;
use App\Actions\ContainerPayment\CreateContainerPayment;
use App\Interfaces\ContainerPaymentRepositoryInterface;
use App\Models\Container;
use App\Models\ContainerPayment;

class ContainerPaymentRepository implements ContainerPaymentRepositoryInterface
{
    public function getContainerPayment(Container $container)
    {
        return GetContainerPayment::run($container);
    }

    public function store(array $data): ContainerPayment
    {
        try {
            return CreateContainerPayment::run($data);
        } catch (\Exception $e) {
            throw new \Exception('Failed to create container: '.$e->getMessage());
        }
    }
}
