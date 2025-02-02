<?php

namespace App\Interfaces\CallCenter;

interface ReceptionRepositoryInterface
{
    public function storeVerification(array $data): void;
}
