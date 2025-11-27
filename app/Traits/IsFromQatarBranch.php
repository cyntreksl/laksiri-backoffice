<?php

namespace App\Traits;

trait IsFromQatarBranch
{
    /**
     * Check if the model belongs to Qatar branch
     */
    public function isFromQatarBranch(): bool
    {
        return $this->branch && strtolower($this->branch->country) === 'qatar';
    }
}
