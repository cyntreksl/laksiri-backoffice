<?php

namespace App\Http\Controllers;

use App\Models\Container;
use App\Services\UnloadingAuditService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContainerUnloadingAuditController extends Controller
{
    protected UnloadingAuditService $auditService;

    public function __construct(UnloadingAuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Display audit logs for a container
     */
    public function index(Request $request, Container $container)
    {
        $filters = $request->only(['action', 'level', 'date_from', 'date_to']);
        
        $auditLogs = $this->auditService->getContainerAuditLogs($container->id, $filters);

        return Inertia::render('Arrival/AuditLogs/Index', [
            'container' => $container,
            'auditLogs' => $auditLogs,
            'filters' => $filters,
        ]);
    }

    /**
     * Get audit logs as JSON (for API or AJAX requests)
     */
    public function getAuditLogs(Request $request, Container $container)
    {
        $filters = $request->only(['action', 'level', 'date_from', 'date_to']);
        
        $auditLogs = $this->auditService->getContainerAuditLogs($container->id, $filters);

        return response()->json([
            'success' => true,
            'data' => $auditLogs,
        ]);
    }

    /**
     * Get audit logs for an HBL
     */
    public function hblAuditLogs(Request $request, int $hblId)
    {
        $auditLogs = $this->auditService->getHBLAuditLogs($hblId);

        return response()->json([
            'success' => true,
            'data' => $auditLogs,
        ]);
    }

    /**
     * Get audit logs for an MHBL
     */
    public function mhblAuditLogs(Request $request, int $mhblId)
    {
        $auditLogs = $this->auditService->getMHBLAuditLogs($mhblId);

        return response()->json([
            'success' => true,
            'data' => $auditLogs,
        ]);
    }

    /**
     * Get audit logs for a package
     */
    public function packageAuditLogs(Request $request, int $packageId)
    {
        $auditLogs = $this->auditService->getPackageAuditLogs($packageId);

        return response()->json([
            'success' => true,
            'data' => $auditLogs,
        ]);
    }
}
