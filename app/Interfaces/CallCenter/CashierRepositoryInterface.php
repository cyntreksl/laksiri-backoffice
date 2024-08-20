<?php

namespace App\Interfaces\CallCenter;

interface CashierRepositoryInterface
{
    public function updatePayment(array $data): void;
}
