<?php

namespace App\Actions\HBL;

use App\Models\Branch;
use App\Models\HBL;
use App\Models\MHBL;
use Illuminate\Support\Facades\Auth;
use Lorisleiva\Actions\Concerns\AsAction;

class GenerateHBLNumber
{
    use AsAction;

    public function handle($currentBranchId)
    {
        $branchCode = $this->getBranchCode($currentBranchId);
        $nextNumber = $this->getNextAvailableNumber($currentBranchId);

        do {
            $hblNumber = strtoupper(str_pad($branchCode, 3, '0', STR_PAD_LEFT)).
                str_pad($nextNumber, 6, '0', STR_PAD_LEFT);

            $exists = HBL::withoutGlobalScopes()->where('hbl_number', $hblNumber)->exists();
            $existsInMHBL = MHBL::withoutGlobalScopes()->where('hbl_number', $hblNumber)->exists();

            $nextNumber++;
        } while ($exists || $existsInMHBL);

        return $hblNumber;
    }

    private function getNextAvailableNumber($branchId)
    {
        $lastHBL = HBL::where('branch_id', $branchId)->latest()->first();
        $lastMHBL = MHBL::where('branch_id', $branchId)->latest()->first();

        $hblNumber = $this->extractNumberFromHBL($lastHBL?->hbl_number);
        $mhblNumber = $this->extractNumberFromHBL($lastMHBL?->hbl_number);

        return max($hblNumber, $mhblNumber) + 1;
    }

    private function extractNumberFromHBL(?string $hblNumber): int
    {
        if (! $hblNumber || strlen($hblNumber) < 6) {
            return 0;
        }

        $numberPart = substr($hblNumber, -6);

        return is_numeric($numberPart) ? (int) $numberPart : 0;
    }

    private function getBranchCode($branchId): string
    {
        $branchId = $branchId ?: Auth::user()->primary_branch_id;
        $branch = Branch::withoutGlobalScopes()->find($branchId);

        return $branch?->branch_code ?? '000';
    }
}
