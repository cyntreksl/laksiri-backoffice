<?php

namespace App\Http\Controllers;

use App\Exports\ManifestClearanceStatusExport;
use App\Models\Branch;
use App\Models\Container;
use App\Models\Scopes\BranchScope;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class ManifestClearanceStatusController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the Manifest Clearance Status report page
     */
    public function index(): Response
    {
        $this->authorize('reports.manifest-clearance-status');

        return Inertia::render('Reports/ManifestClearanceStatus', [
            'branches' => $this->getBranches(),
        ]);
    }

    /**
     * Get branches for filter
     */
    private function getBranches(): array
    {
        return Branch::select('id', 'name', 'branch_code')
            ->orderBy('name')
            ->get()
            ->map(fn($branch) => [
                'value' => $branch->id,
                'label' => $branch->name,
                'code' => $branch->branch_code ?? ''
            ])
            ->toArray();
    }

    /**
     * Get Manifest Clearance Status report data with filters
     */
    public function getData(Request $request): JsonResponse
    {
        $this->authorize('reports.manifest-clearance-status');

        $query = Container::withoutGlobalScope(BranchScope::class)
            ->select([
                'containers.id',
                'containers.manifest_number',
                'containers.container_number',
                'containers.branch_id',
                'containers.estimated_time_of_arrival',
                'containers.created_at'
            ])
            ->whereNotNull('manifest_number')
            ->with(['branch:id,name'])
            // Only include containers that have HBL packages linked to them
            ->whereExists(function ($subQuery) {
                $subQuery->select(DB::raw(1))
                    ->from('container_hbl_package')
                    ->join('hbl_packages', 'container_hbl_package.hbl_package_id', '=', 'hbl_packages.id')
                    ->join('hbl', 'hbl_packages.hbl_id', '=', 'hbl.id')
                    ->whereColumn('container_hbl_package.container_id', 'containers.id')
                    ->whereNull('hbl_packages.deleted_at')
                    ->whereNull('hbl.deleted_at');
            });

        // Apply filters
        $this->applyFilters($query, $request);

        // Debug: Log the query when pending manifests is checked
        if ($request->boolean('pending_manifests')) {
            \Log::info('Pending Manifests Query', [
                'sql' => $query->toSql(),
                'bindings' => $query->getBindings()
            ]);
        }

        // Get total count before pagination
        $totalRecords = $query->count();

        // Calculate summary statistics
        $stats = $this->calculateStats($query);

        // Apply sorting
        $sortField = $request->input('sort_field', 'manifest_number');
        $sortOrder = $request->input('sort_order', 'asc');

        $sortableFields = [
            'manifest_number' => 'manifest_number',
            'agent_name' => 'branches.name',
            'container_number' => 'container_number',
            'arrival_date' => 'estimated_time_of_arrival',
        ];

        if (isset($sortableFields[$sortField])) {
            if ($sortField === 'agent_name') {
                $query->join('branches', 'containers.branch_id', '=', 'branches.id')
                      ->orderBy('branches.name', $sortOrder);
            } else {
                $query->orderBy($sortableFields[$sortField], $sortOrder);
            }
        } else {
            $query->orderBy('manifest_number', 'asc');
        }

        // Apply pagination
        $perPage = $request->input('per_page', 25);
        $page = $request->input('page', 1);
        
        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        // Transform data
        $data = $paginator->map(function ($container) {
            return $this->transformRecord($container);
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $paginator->total(),
            'stats' => $stats,
            'per_page' => $paginator->perPage(),
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
        ]);
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, Request $request): void
    {
        // Date range filter
        if ($request->filled('date_from')) {
            $query->where('estimated_time_of_arrival', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('estimated_time_of_arrival', '<=', $request->input('date_to') . ' 23:59:59');
        }

        // Branch filter (Agent Code)
        if ($request->filled('branch_id_from')) {
            $query->where('branch_id', '>=', $request->input('branch_id_from'));
        }

        if ($request->filled('branch_id_to')) {
            $query->where('branch_id', '<=', $request->input('branch_id_to'));
        }

        // Manifest number range filter
        if ($request->filled('manifest_number_from')) {
            $query->where('manifest_number', '>=', $request->input('manifest_number_from'));
        }

        if ($request->filled('manifest_number_to')) {
            $query->where('manifest_number', '<=', $request->input('manifest_number_to'));
        }

        // Pending manifests filter - this is the key filter
        if ($request->boolean('pending_manifests')) {
            // Only show containers that have NO unclear consignees (Unclear <= 0)
            // This means all consignees have gate passes issued
            $query->whereRaw('
                NOT EXISTS (
                    SELECT 1 
                    FROM container_hbl_package chp
                    JOIN hbl_packages hp ON chp.hbl_package_id = hp.id
                    JOIN hbl h ON hp.hbl_id = h.id
                    LEFT JOIN examinations e ON h.id = e.hbl_id
                    WHERE chp.container_id = containers.id
                    AND hp.deleted_at IS NULL
                    AND h.deleted_at IS NULL
                    AND (e.is_issued_gate_pass = 0 OR e.is_issued_gate_pass IS NULL)
                )
            ');
        }

        // General search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('manifest_number', 'like', "%{$search}%")
                    ->orWhere('container_number', 'like', "%{$search}%");
            });
        }
    }

    /**
     * Calculate summary statistics
     */
    private function calculateStats($query): array
    {
        $containerIds = (clone $query)->pluck('id');
        
        if ($containerIds->isEmpty()) {
            return [
                'total_manifests' => 0,
                'total_consignees_received' => 0,
                'total_consignees_cleared' => 0,
                'total_consignees_unclear' => 0,
            ];
        }

        // Get consignee statistics
        $consigneeStats = DB::table('container_hbl_package')
            ->join('hbl_packages', 'container_hbl_package.hbl_package_id', '=', 'hbl_packages.id')
            ->join('hbl', 'hbl_packages.hbl_id', '=', 'hbl.id')
            ->leftJoin('examinations', 'hbl.id', '=', 'examinations.hbl_id')
            ->whereIn('container_hbl_package.container_id', $containerIds)
            ->whereNull('hbl_packages.deleted_at')
            ->whereNull('hbl.deleted_at')
            ->selectRaw('
                COUNT(DISTINCT hbl.id) as total_consignees,
                COUNT(DISTINCT CASE WHEN examinations.is_issued_gate_pass = 1 THEN hbl.id END) as cleared_consignees,
                COUNT(DISTINCT CASE WHEN examinations.is_issued_gate_pass = 0 OR examinations.is_issued_gate_pass IS NULL THEN hbl.id END) as unclear_consignees
            ')
            ->first();

        return [
            'total_manifests' => $containerIds->count(),
            'total_consignees_received' => $consigneeStats->total_consignees ?? 0,
            'total_consignees_cleared' => $consigneeStats->cleared_consignees ?? 0,
            'total_consignees_unclear' => $consigneeStats->unclear_consignees ?? 0,
        ];
    }

    /**
     * Transform Container record for response
     */
    private function transformRecord(Container $container): array
    {
        // Get consignee counts for this container
        $consigneeStats = DB::table('container_hbl_package')
            ->join('hbl_packages', 'container_hbl_package.hbl_package_id', '=', 'hbl_packages.id')
            ->join('hbl', 'hbl_packages.hbl_id', '=', 'hbl.id')
            ->leftJoin('examinations', 'hbl.id', '=', 'examinations.hbl_id')
            ->where('container_hbl_package.container_id', $container->id)
            ->whereNull('hbl_packages.deleted_at')
            ->whereNull('hbl.deleted_at')
            ->selectRaw('
                COUNT(DISTINCT hbl.id) as total_consignees,
                COALESCE(COUNT(DISTINCT CASE WHEN examinations.is_issued_gate_pass = 1 THEN hbl.id END), 0) as cleared_consignees,
                COALESCE(COUNT(DISTINCT CASE WHEN examinations.is_issued_gate_pass = 0 OR examinations.is_issued_gate_pass IS NULL THEN hbl.id END), 0) as unclear_consignees
            ')
            ->first();

        // Ensure all values are integers, explicitly convert to int and ensure minimum 0
        $totalConsignees = (int) ($consigneeStats->total_consignees ?? 0);
        $clearedConsignees = (int) ($consigneeStats->cleared_consignees ?? 0);
        $unclearConsignees = (int) ($consigneeStats->unclear_consignees ?? 0);

        // If we have consignees but no examinations, all should be unclear
        if ($totalConsignees > 0 && ($clearedConsignees + $unclearConsignees) == 0) {
            $unclearConsignees = $totalConsignees;
        }

        // Double check: if we have consignees but cleared is null/empty, set to 0
        if ($totalConsignees > 0 && $clearedConsignees === null) {
            $clearedConsignees = 0;
        }

        return [
            'id' => $container->id,
            'ref_no' => $container->manifest_number,
            'agent_name' => $container->branch?->name ?? '',
            'container_no_1' => $container->container_number ?? '',
            'container_no_2' => '', // Empty as per requirement
            'container_no_3' => '', // Empty as per requirement
            'arrival_date' => $container->estimated_time_of_arrival?->format('d/m/Y'),
            'consignees_received' => (int) $totalConsignees,
            'consignees_clear' => (int) $clearedConsignees,
            'consignees_unclear' => (int) $unclearConsignees,
        ];
    }

    /**
     * Export Manifest Clearance Status report
     */
    public function export(Request $request)
    {
        $this->authorize('reports.manifest-clearance-status');

        try {
            $format = $request->input('format', 'xlsx');
            $filename = 'manifest-clearance-status-' . date('Y-m-d-His');

            // Debug: Log the request parameters
            \Log::info('Manifest Clearance Status Export Request', [
                'format' => $format,
                'params' => $request->all()
            ]);

            // Test query first to ensure data exists
            $testQuery = Container::withoutGlobalScope(BranchScope::class)
                ->whereNotNull('manifest_number')
                ->count();

            \Log::info('Manifest Clearance Status Export Data Check', [
                'total_containers_with_manifest' => $testQuery
            ]);

            if ($testQuery === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No manifest data found to export'
                ], 404);
            }

            // Create export instance
            $export = new ManifestClearanceStatusExport($request);

            // Handle different formats
            switch ($format) {
                case 'pdf':
                    return Excel::download($export, $filename . '.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
                case 'csv':
                    return Excel::download($export, $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
                default:
                    return Excel::download($export, $filename . '.xlsx');
            }

        } catch (\Exception $e) {
            \Log::error('Manifest Clearance Status Export Error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }
}