<?php

namespace App\Interfaces;

use App\Models\HBL;

interface CashSettlementInterface
{
    public function getSummery(array $filters = []);

    public function getDuePaymentSummery(array $filters = []);

    public function cashReceived(array $hblIds);

    public function updatePayment(array $data, HBL $hbl);

    public function export(array $filters);

    public function duePaymentExport(array $filters);
}
