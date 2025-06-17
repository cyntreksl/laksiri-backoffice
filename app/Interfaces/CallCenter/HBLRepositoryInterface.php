<?php

namespace App\Interfaces\CallCenter;

use App\Models\HBL;

interface HBLRepositoryInterface
{
    public function getHBLs();

    public function createAndIssueToken(HBL $hbl);

    public function createAndIssueTokenWithVerification(HBL $hbl, array $verificationData);

    public function generateTokenPDF($tokenId, $type = 'download');

    public function getDoorToDoorHBL(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = []);
}
