<?php

namespace App\Interfaces\CallCenter;

use App\Models\CustomerQueue;

interface ExaminationRepositoryInterface
{
    public function releaseHBL(array $data): void;

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = []);

    public function markAsDeparted(CustomerQueue $customerQueue);

    public function returnPackagesToBond(array $data): void;

    public function completeToken(array $data): void;
}
