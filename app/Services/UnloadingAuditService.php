<?php

namespace App\Services;

use App\Models\Container;
use App\Models\ContainerUnloadingAuditLog;
use App\Models\HBL;
use App\Models\HBLPackage;
use App\Models\MHBL;
use Illuminate\Support\Facades\Request;

class UnloadingAuditService
{
    /**
     * Log a package-level unload/reload action
     */
    public function logPackageAction(
        Container $container,
        HBLPackage $package,
        string $action
    ): ContainerUnloadingAuditLog {
        return ContainerUnloadingAuditLog::create([
            'container_id' => $container->id,
            'action' => $action,
            'level' => 'package',
            'hbl_package_id' => $package->id,
            'hbl_id' => $package->hbl_id,
            'mhbl_id' => $package->hbl->mhbl_id ?? null,
            'hbl_number' => $package->hbl->hbl_number ?? null,
            'mhbl_number' => $package->hbl->mhbl->hbl_number ?? null,
            'package_count' => 1,
            'package_details' => [
                'package_id' => $package->id,
                'package_type' => $package->package_type,
                'quantity' => $package->quantity,
                'weight' => $package->weight,
                'volume' => $package->volume,
            ],
            'performed_by' => auth()->id(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Log an HBL-level unload/reload action (multiple packages at once)
     */
    public function logHBLAction(
        Container $container,
        HBL $hbl,
        array $packages,
        string $action
    ): ContainerUnloadingAuditLog {
        $packagesData = collect($packages)->map(function ($package) {
            return [
                'package_id' => $package->id ?? $package['id'],
                'package_type' => $package->package_type ?? $package['package_type'],
                'quantity' => $package->quantity ?? $package['quantity'],
                'weight' => $package->weight ?? $package['weight'],
                'volume' => $package->volume ?? $package['volume'],
            ];
        })->toArray();

        return ContainerUnloadingAuditLog::create([
            'container_id' => $container->id,
            'action' => $action,
            'level' => 'hbl',
            'hbl_id' => $hbl->id,
            'mhbl_id' => $hbl->mhbl_id ?? null,
            'hbl_number' => $hbl->hbl_number,
            'mhbl_number' => $hbl->mhbl->hbl_number ?? null,
            'package_count' => count($packages),
            'packages_affected' => $packagesData,
            'performed_by' => auth()->id(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Log an MHBL-level unload/reload action (multiple packages at once)
     */
    public function logMHBLAction(
        Container $container,
        MHBL $mhbl,
        array $packages,
        string $action
    ): ContainerUnloadingAuditLog {
        $packagesData = collect($packages)->map(function ($package) {
            return [
                'package_id' => $package->id ?? $package['id'],
                'hbl_number' => $package->hbl->hbl_number ?? $package['hbl']['hbl_number'] ?? null,
                'package_type' => $package->package_type ?? $package['package_type'],
                'quantity' => $package->quantity ?? $package['quantity'],
                'weight' => $package->weight ?? $package['weight'],
                'volume' => $package->volume ?? $package['volume'],
            ];
        })->toArray();

        return ContainerUnloadingAuditLog::create([
            'container_id' => $container->id,
            'action' => $action,
            'level' => 'mhbl',
            'mhbl_id' => $mhbl->id,
            'mhbl_number' => $mhbl->hbl_number,
            'package_count' => count($packages),
            'packages_affected' => $packagesData,
            'performed_by' => auth()->id(),
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
        ]);
    }

    /**
     * Get audit logs for a container
     */
    public function getContainerAuditLogs(int $containerId, array $filters = [])
    {
        $query = ContainerUnloadingAuditLog::query()
            ->with(['performedBy', 'hblPackage', 'hbl', 'mhbl'])
            ->forContainer($containerId)
            ->orderBy('created_at', 'desc');

        if (isset($filters['action'])) {
            $query->where('action', $filters['action']);
        }

        if (isset($filters['level'])) {
            $query->byLevel($filters['level']);
        }

        if (isset($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        return $query->get();
    }

    /**
     * Get audit logs for an HBL
     */
    public function getHBLAuditLogs(int $hblId)
    {
        return ContainerUnloadingAuditLog::query()
            ->with(['performedBy', 'container'])
            ->where('hbl_id', $hblId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get audit logs for an MHBL
     */
    public function getMHBLAuditLogs(int $mhblId)
    {
        return ContainerUnloadingAuditLog::query()
            ->with(['performedBy', 'container'])
            ->where('mhbl_id', $mhblId)
            ->orderBy('created_at', 'desc')
            ->get();
    }

    /**
     * Get audit logs for a specific package
     */
    public function getPackageAuditLogs(int $packageId)
    {
        return ContainerUnloadingAuditLog::query()
            ->with(['performedBy', 'container'])
            ->where('hbl_package_id', $packageId)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
