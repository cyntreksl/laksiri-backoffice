<?php

namespace App\Interfaces\CallCenter;

interface VerificationRepositoryInterface
{
    public function storeVerification(array $data): void;
}
