<?php

namespace App\Repositories\Finance;

use App\Actions\HBL\GetHBLs;
use App\Actions\HBL\GetHBLsByIDs;
use App\Actions\HBL\GetHBLsWithPackages;
use App\Factory\HBL\FilterFactory;
use App\Http\Resources\HBLResource;
use App\Interfaces\Finance\HBLRepositoryInterface;
use App\Interfaces\GridJsInterface;
use App\Models\HBL;
use App\Models\Scopes\BranchScope;
use App\Traits\ResponseAPI;
use Illuminate\Support\Facades\Auth;

class HBLRepository implements GridJsInterface, HBLRepositoryInterface
{
    use ResponseAPI;

    public function getApproveHBLs()
    {
        return GetHBLs::run();
    }

    public function getHBLsWithPackages()
    {
        return GetHBLsWithPackages::run();
    }

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = HBL::query()
            ->withoutGlobalScope(BranchScope::class)
            ->where('is_finance_release_approved', '=', false)
            ->where('is_released', '=', false)
            ->where('system_status', '>', 4.2);

        if (! empty($search)) {
            $query->whereAny([
                'reference',
                'hbl_number',
                'hbl_name',
                'contact_number',
                'consignee_name',
                'consignee_nic',
                'consignee_contact',
                'iq_number',
                'nic',
                'email',
            ], 'like', '%'.$search.'%');
        }

        // apply filters
        FilterFactory::apply($query, $filters);

        $countQuery = $query;
        $totalRecords = $countQuery->count();

        $hbls = $query->orderBy($order, $direction)->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'data' => HBLResource::collection($hbls),
            'meta' => [
                'total' => $totalRecords,
                'page' => $offset,
                'perPage' => $limit,
                'lastPage' => ceil($totalRecords / $limit),
            ],
        ]);
    }

    public function approvedDataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = HBL::query()
            ->withoutGlobalScope(BranchScope::class)
            ->where('is_finance_release_approved', '=', true);

        if (! empty($search)) {
            $query->whereAny([
                'reference',
                'hbl_number',
                'hbl_name',
                'contact_number',
                'consignee_name',
                'consignee_nic',
                'consignee_contact',
                'iq_number',
                'nic',
                'email',
            ], 'like', '%'.$search.'%');
        }

        // apply filters
        FilterFactory::apply($query, $filters);

        $countQuery = $query;
        $totalRecords = $countQuery->count();

        $hbls = $query->orderBy($order, $direction)->paginate($limit, ['*'], 'page', $offset);

        return response()->json([
            'data' => HBLResource::collection($hbls),
            'meta' => [
                'total' => $totalRecords,
                'page' => $offset,
                'perPage' => $limit,
                'lastPage' => ceil($totalRecords / $limit),
            ],
        ]);
    }

    public function financeApproved(array $hblIds, ?bool $consentGiven = null, ?string $consentNote = null)
    {
        $hblList = GetHBLsByIDs::run($hblIds);
        $hblsWithMissingReachedDate = [];

        // First pass: Check for HBLs with missing reached dates
        foreach ($hblList as $hbl) {
            $hasReachedDate = $this->checkHBLReachedDate($hbl);
            
            if (!$hasReachedDate) {
                $hblsWithMissingReachedDate[] = [
                    'id' => $hbl->id,
                    'hbl_number' => $hbl->hbl_number ?? $hbl->hbl,
                    'hbl_name' => $hbl->hbl_name,
                ];
            }
        }

        // If there are HBLs with missing reached dates and no consent given, return warning
        if (!empty($hblsWithMissingReachedDate) && $consentGiven !== true) {
            return response()->json([
                'status' => 'warning',
                'message' => 'Some HBLs have missing container reached dates. Demurrage calculation may be affected.',
                'requires_consent' => true,
                'hbls_with_missing_dates' => $hblsWithMissingReachedDate,
            ], 200);
        }

        // Proceed with approval
        foreach ($hblList as $hbl) {
            $hbl = HBL::find($hbl->id);
            $hbl->is_finance_release_approved = true;
            $hbl->finance_release_approved_by = Auth::user()->id;
            $hbl->finance_release_approved_date = now();
            
            // Store consent if reached date is missing
            $hasReachedDate = $this->checkHBLReachedDate($hbl);
            if (!$hasReachedDate && $consentGiven === true) {
                $hbl->demurrage_consent_given = true;
                $hbl->demurrage_consent_by = Auth::user()->id;
                $hbl->demurrage_consent_at = now();
                $hbl->demurrage_consent_note = $consentNote ?? 'Approved without container reached date';
            }
            
            $hbl->addStatus('Approved by Accountant');
            $hbl->save();
        }

        return $this->success('Approved Successfully', []);
    }

    /**
     * Check if HBL has a valid container reached date
     */
    private function checkHBLReachedDate(HBL $hbl): bool
    {
        $packages = $hbl->packages;
        
        foreach ($packages as $package) {
            // Check regular containers
            $container = $package->containers()->withoutGlobalScopes()->first();
            if ($container) {
                $reachedDate = $container->arrived_at_primary_warehouse ?? $container->reached_date;
                if ($reachedDate) {
                    return true;
                }
            }
            
            // Check duplicate containers
            $container = $package->duplicate_containers()->withoutGlobalScopes()->first();
            if ($container) {
                $reachedDate = $container->arrived_at_primary_warehouse ?? $container->reached_date;
                if ($reachedDate) {
                    return true;
                }
            }
        }
        
        return false;
    }

    public function removeFinanceApproval(array $hblIds)
    {
        $hblList = GetHBLsByIDs::run($hblIds);

        foreach ($hblList as $hbl) {
            $hbl = HBL::find($hbl->id);
            $hbl->is_finance_release_approved = false;
            $hbl->finance_release_approved_by = null;
            $hbl->finance_release_approved_date = null;
            $hbl->addStatus('Approve Removed by Accountant');

            $hbl->save();
        }

        return $this->success('Remove Approval Successfully', []);
    }
}
