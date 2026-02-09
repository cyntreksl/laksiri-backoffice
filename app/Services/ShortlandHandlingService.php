<?php

namespace App\Services;

use App\Models\HBL;
use App\Models\HBLPackage;
use App\Models\UnloadingIssue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ShortlandHandlingService
{
    /**
     * Mark entire HBL and all its packages as Shortland
     */
    public function markAsShortland(array $packageIds, int $userId): void
    {
        DB::transaction(function () use ($packageIds, $userId) {
            // Get all unique HBLs from the selected packages
            $hblIds = HBLPackage::whereIn('id', $packageIds)
                ->pluck('hbl_id')
                ->unique();

            foreach ($hblIds as $hblId) {
                $hbl = HBL::find($hblId);

                if (!$hbl) {
                    continue;
                }

                // Mark HBL as Shortland (only if not already marked)
                if (!$hbl->is_shortland) {
                    $hbl->update([
                        'is_shortland' => true,
                        'is_shortland_fixed' => false,
                        'shortland_marked_at' => now(),
                        'shortland_marked_by' => $userId,
                    ]);

                    Log::info("HBL {$hbl->hbl_number} marked as Shortland", [
                        'hbl_id' => $hbl->id,
                        'marked_by' => $userId,
                    ]);
                }

                // Mark ALL packages under this HBL as Shortland
                HBLPackage::where('hbl_id', $hblId)
                    ->update([
                        'is_shortland' => true,
                        'is_shortland_fixed' => false,
                        'shortland_marked_at' => now(),
                        'shortland_marked_by' => $userId,
                    ]);

                Log::info("All packages for HBL {$hbl->hbl_number} marked as Shortland", [
                    'hbl_id' => $hbl->id,
                    'total_packages' => $hbl->packages()->count(),
                ]);
            }
        });
    }

    /**
     * Check and auto-fix Shortland when packages are unloaded
     */
    public function checkAndAutoFixShortland(HBLPackage $package): void
    {
        $hbl = $package->hbl;

        if (!$hbl || !$hbl->is_shortland || $hbl->is_shortland_fixed) {
            return;
        }

        // Check if all packages with Shortland issues are now unloaded
        $shortlandPackages = HBLPackage::where('hbl_id', $hbl->id)
            ->where('is_shortland', true)
            ->get();

        $allShortlandPackagesUnloaded = $shortlandPackages->every(function ($pkg) {
            return $pkg->is_unloaded;
        });

        if ($allShortlandPackagesUnloaded) {
            DB::transaction(function () use ($hbl, $shortlandPackages) {
                // Mark HBL as Shortland Fixed
                $hbl->update([
                    'is_shortland_fixed' => true,
                    'shortland_fixed_at' => now(),
                ]);

                // Mark all Shortland packages as fixed
                HBLPackage::where('hbl_id', $hbl->id)
                    ->where('is_shortland', true)
                    ->update([
                        'is_shortland_fixed' => true,
                        'shortland_fixed_at' => now(),
                    ]);

                // Mark all Shortland unloading issues as fixed
                $packageIds = $shortlandPackages->pluck('id');
                UnloadingIssue::whereIn('hbl_package_id', $packageIds)
                    ->where('type', 'Shortland')
                    ->where('is_fixed', false)
                    ->update([
                        'is_fixed' => true,
                    ]);

                Log::info("Shortland auto-fixed for HBL {$hbl->hbl_number}", [
                    'hbl_id' => $hbl->id,
                    'fixed_by' => auth()->id(),
                    'packages_count' => $packageIds->count(),
                ]);
            });
        }
    }

    /**
     * Get Shortland status for an HBL
     */
    public function getShortlandStatus(HBL $hbl): array
    {
        $totalPackages = $hbl->packages()->count();
        $shortlandPackages = $hbl->packages()->where('is_shortland', true)->count();
        $unloadedShortlandPackages = $hbl->packages()
            ->where('is_shortland', true)
            ->where('is_unloaded', true)
            ->count();

        return [
            'is_shortland' => $hbl->is_shortland,
            'is_shortland_fixed' => $hbl->is_shortland_fixed,
            'total_packages' => $totalPackages,
            'shortland_packages' => $shortlandPackages,
            'unloaded_shortland_packages' => $unloadedShortlandPackages,
            'pending_shortland_packages' => $shortlandPackages - $unloadedShortlandPackages,
            'shortland_marked_at' => $hbl->shortland_marked_at,
            'shortland_fixed_at' => $hbl->shortland_fixed_at,
        ];
    }
}
