<?php

namespace App\Interfaces\CallCenter;

interface BonedAreaRepositoryInterface
{
    public function releasePackage(array $data): void;
}
