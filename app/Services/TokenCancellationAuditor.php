<?php

namespace App\Services;

use App\Models\HBLPackage;
use App\Models\Token;
use App\Models\TokenCancellation;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class TokenCancellationAuditor
{
    /**
     * Create a comprehensive audit log entry for token cancellation.
     *
     * @param Token $token
     * @param string $cancellationReason
     * @param User $cancelledBy
     * @param bool $invoiceCancelled
     * @param bool $gatePassCancelled
     * @return TokenCancellation
     */
    public function createAuditLog(
        Token $token,
        string $cancellationReason,
        User $cancelledBy,
        bool $invoiceCancelled,
        bool $gatePassCancelled
    ): TokenCancellation {
        // Collect HBL package status and warnings
        $packageStatusData = $this->collectHBLPackageStatus($token);

        // Determine token status at cancellation
        $tokenStatus = $this->determineTokenStatus($token);

        // Collect post-cancellation impacts
        $postCancellationImpacts = $this->collectPostCancellationImpacts(
            $invoiceCancelled,
            $gatePassCancelled,
            $packageStatusData
        );

        // Create the audit log entry
        $auditLog = TokenCancellation::create([
            'token_id' => $token->id,
            'cancelled_by' => $cancelledBy->id,
            'cancellation_reason' => $cancellationReason,
            'token_status_at_cancellation' => $tokenStatus,
            'invoice_cancelled' => $invoiceCancelled,
            'gate_pass_cancelled' => $gatePassCancelled,
            'hbl_package_status' => $packageStatusData['package_status'],
            'warnings_shown' => $packageStatusData['warnings'],
            'post_cancellation_impacts' => $postCancellationImpacts,
        ]);

        Log::info('Token cancellation audit log created', [
            'token_id' => $token->id,
            'audit_log_id' => $auditLog->id,
            'cancelled_by' => $cancelledBy->id,
        ]);

        return $auditLog;
    }

    /**
     * Collect HBL package status and location information.
     *
     * @param Token $token
     * @return array
     */
    public function collectHBLPackageStatus(Token $token): array
    {
        $hbl = $token->hbl;

        if (!$hbl) {
            return [
                'package_status' => [
                    'packages' => [],
                    'all_in_bond' => true,
                    'packages_outside_bond' => [],
                ],
                'warnings' => [],
            ];
        }

        $packages = $hbl->packages;
        $packageData = [];
        $packagesOutsideBond = [];
        $warnings = [];

        foreach ($packages as $package) {
            $isInBond = $this->isPackageInBondArea($package);
            $isReleased = $this->isPackageReleased($package);

            $packageInfo = [
                'package_id' => $package->id,
                'package_number' => "PKG-{$package->id}",
                'location' => $isInBond ? 'bond_area' : 'released',
                'status' => $this->getPackageStatus($package),
                'released' => $isReleased,
            ];

            if ($isReleased && $package->unloaded_at) {
                $packageInfo['released_at'] = $package->unloaded_at->toIso8601String();
            }

            $packageData[] = $packageInfo;

            // Track packages outside bond area
            if (!$isInBond) {
                $packagesOutsideBond[] = $packageInfo['package_number'];
                $warnings[] = "Package {$packageInfo['package_number']} is outside bond area - must be returned";
            }
        }

        $allInBond = empty($packagesOutsideBond);

        return [
            'package_status' => [
                'packages' => $packageData,
                'all_in_bond' => $allInBond,
                'packages_outside_bond' => $packagesOutsideBond,
            ],
            'warnings' => $warnings,
        ];
    }

    /**
     * Determine if a package is in the bond area.
     *
     * @param HBLPackage $package
     * @return bool
     */
    private function isPackageInBondArea(HBLPackage $package): bool
    {
        // A package is in bond area if:
        // 1. It has been unloaded (is_unloaded = true)
        // 2. It has NOT been released/departed (no unloaded_at or is_de_unloaded = false)
        // 3. It is not marked as hold or RTF
        
        // If package is not unloaded yet, it's not in bond area
        if (!$package->is_unloaded) {
            return false;
        }

        // If package has been de-unloaded (released from bond), it's not in bond area
        if ($package->is_de_unloaded) {
            return false;
        }

        // Package is in bond area
        return true;
    }

    /**
     * Determine if a package has been released.
     *
     * @param HBLPackage $package
     * @return bool
     */
    private function isPackageReleased(HBLPackage $package): bool
    {
        return $package->is_de_unloaded === true || $package->is_de_unloaded === 1;
    }

    /**
     * Get the current status of a package.
     *
     * @param HBLPackage $package
     * @return string
     */
    private function getPackageStatus(HBLPackage $package): string
    {
        if ($package->is_de_unloaded) {
            return 'departed';
        }

        if ($package->is_unloaded) {
            return 'in_bond';
        }

        if ($package->is_loaded) {
            return 'loaded';
        }

        return 'pending';
    }

    /**
     * Determine the token status at the time of cancellation.
     *
     * @param Token $token
     * @return string
     */
    private function determineTokenStatus(Token $token): string
    {
        if ($token->isCompleted()) {
            return 'completed';
        }

        return 'in_progress';
    }

    /**
     * Collect post-cancellation impacts.
     *
     * @param bool $invoiceCancelled
     * @param bool $gatePassCancelled
     * @param array $packageStatusData
     * @return array
     */
    private function collectPostCancellationImpacts(
        bool $invoiceCancelled,
        bool $gatePassCancelled,
        array $packageStatusData
    ): array {
        $impacts = [];

        if ($invoiceCancelled) {
            $impacts[] = 'Invoice cancelled - financial records updated';
        }

        if ($gatePassCancelled) {
            $impacts[] = 'Gate pass cancelled - access authorization revoked';
        }

        $impacts[] = 'Token removed from all queues';

        if (!$packageStatusData['package_status']['all_in_bond']) {
            $impacts[] = 'Operational risk: Packages outside bond area require manual return';
        }

        return $impacts;
    }
}
