<?php

namespace App\Interfaces;

use App\Models\Tax;

interface TaxRepositoryInterface
{
    public function createTax(array $data);

    public function updateTax(Tax $tax, array $data);

    public function destroyTax(Tax $tax);
}
