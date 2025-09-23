<?php

namespace App\Interfaces;

use App\Models\HBL;

interface CashSettlementInterface
{
    public function getSummary(array $filters = []);

    public function getDuePaymentSummary(array $filters = []);

    public function cashReceived(array $hblIds);

    public function updatePayment(array $data, HBL $hbl);

    public function export(array $filters);

    public function duePaymentExport(array $filters);
}
