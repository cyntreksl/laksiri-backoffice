<?php

namespace App\Interfaces\CallCenter;

interface ExaminationRepositoryInterface
{
    public function releaseHBL(array $data): void;
}
