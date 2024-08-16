<?php

namespace App\Repositories\CallCenter;

use App\Actions\Verification\CreateVerification;
use App\Interfaces\CallCenter\VerificationRepositoryInterface;

class VerificationRepository implements VerificationRepositoryInterface
{
    public function storeVerification(array $data): void
    {
        try {
            CreateVerification::run($data);
        } catch (\Exception $e) {
            throw new \Exception('Failed to verified: '.$e->getMessage());
        }
    }
}
