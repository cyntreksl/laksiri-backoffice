<?php

namespace App\Http\Controllers;

use App\Enum\CargoType;
use App\Enum\ContainerStatus;
use App\Models\Container;
use App\Models\Branch;
use App\Exports\ShipmentReportExport;
use App\Models\Scopes\BranchScope;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class ShipmentReportController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the Shipment report page
     */
    public function index(): Response
    {
        $this->authorize('reports.shipment');

        return Inertia::render('Reports/ShipmentReport', [
            'cargoTypes' => CargoType::cases(),
            'containerStatuses' => ContainerStatus::cases(),
            'branches' => $this->getBranches(),
        ]);
    }

    /**
     * Get branches for filter
     */
    private function getBranches(): array
    {
        return Branch::select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn($branch) => [
                'value' => $branch->id,
                'label' => $branch->name
            ])
            ->toArray();
    }

    /**
     * Get Shipment report data with filters
     */
    public function getData(Request $request): JsonResponse
    {
        try {
            $this->authorize('reports.shipment');

            $query = Container::withoutGlobalScope(BranchScope::class)
                ->select([
                    'containers.id',
                    'containers.reference',
                    'containers.cargo_type',
                    'containers.container_number',
                    'containers.bl_number',
                    'containers.awb_number',
                    'containers.status',
                    'containers.branch_id',
                    'containers.target_warehouse',
                    'containers.loading_started_at',
                    'containers.loading_ended_at',
                    'containers.unloading_started_at',
                    'containers.unloading_ended_at',
                    'containers.reached_date',
                    'containers.arrived_at_primary_warehouse',
                    'containers.departed_at_primary_warehouse',
                    'containers.created_at',
                ])
                ->with([
                    'branch:id,name',
                    'warehouse:id,name',
                ]);

            // Apply filters
            $this->applyFilters($query, $request);

            // Get total count
            $totalRecords = $query->count();

            // Calculate stats
            $stats = $this->calculateStats($query);

            // Apply sorting
            $sortField = $request->input('sort_field', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');

            $sortableFields = [
                'reference' => 'reference',
                'cargo_type' => 'cargo_type',
                'status' => 'status',
                'loading_started_at' => 'loading_started_at',
                'unloading_started_at' => 'unloading_started_at',
                'reached_date' => 'reached_date',
                'arrived_at_primary_warehouse' => 'arrived_at_primary_warehouse',
                'created_at' => 'created_at',
            ];

            $dbSortField = $sortableFields[$sortField] ?? 'created_at';
            $query->orderBy($dbSortField, $sortOrder);

            // Pagination
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
            ], 200, [], JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            \Log::error('Error in Shipment Report getData: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load shipment report data',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, Request $request): void
    {
        // Loaded Date Range
        if ($request->filled('loaded_date_from')) {
            $query->where('loading_started_at', '>=', $request->input('loaded_date_from'));
        }

        if ($request->filled('loaded_date_to')) {
            $query->where('loading_started_at', '<=', $request->input('loaded_date_to') . ' 23:59:59');
        }

        // Unloaded/Destuff Date Range
        if ($request->filled('unloaded_date_from')) {
            $query->where('unloading_started_at', '>=', $request->input('unloaded_date_from'));
        }

        if ($request->filled('unloaded_date_to')) {
            $query->where('unloading_started_at', '<=', $request->input('unloaded_date_to') . ' 23:59:59');
        }

        // Reached Date Range
        if ($request->filled('reached_date_from')) {
            $query->where('reached_date', '>=', $request->input('reached_date_from'));
        }

        if ($request->filled('reached_date_to')) {
            $query->where('reached_date', '<=', $request->input('reached_date_to') . ' 23:59:59');
        }

        // Arrival Date Range
        if ($request->filled('arrival_date_from')) {
            $query->where('arrived_at_primary_warehouse', '>=', $request->input('arrival_date_from'));
        }

        if ($request->filled('arrival_date_to')) {
            $query->where('arrived_at_primary_warehouse', '<=', $request->input('arrival_date_to') . ' 23:59:59');
        }

        // Release/Departure Date Range
        if ($request->filled('release_date_from')) {
            $query->where('departed_at_primary_warehouse', '>=', $request->input('release_date_from'));
        }

        if ($request->filled('release_date_to')) {
            $query->where('departed_at_primary_warehouse', '<=', $request->input('release_date_to') . ' 23:59:59');
        }

        // Branch/Agent filter
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        // Cargo Type filter
        if ($request->filled('cargo_type')) {
            $query->where('cargo_type', $request->input('cargo_type'));
        }

        // Container Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        // General search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('reference', 'like', "%{$search}%")
                    ->orWhere('container_number', 'like', "%{$search}%")
                    ->orWhere('bl_number', 'like', "%{$search}%")
                    ->orWhere('awb_number', 'like', "%{$search}%");
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
                'total_shipments' => 0,
                'total_packages' => 0,
                'total_weight' => '0.00',
                'total_cbm' => '0.00',
            ];
        }

        // Get package stats
        $packageStats = \DB::table('container_hbl_package')
            ->join('hbl_packages', 'container_hbl_package.hbl_package_id', '=', 'hbl_packages.id')
            ->whereIn('container_hbl_package.container_id', $containerIds)
            ->whereNull('hbl_packages.deleted_at')
            ->selectRaw('
                COUNT(DISTINCT hbl_packages.id) as total_packages,
                COALESCE(SUM(hbl_packages.weight), 0) as total_weight,
                COALESCE(SUM(hbl_packages.volume), 0) as total_cbm
            ')
            ->first();

        return [
            'total_shipments' => $containerIds->count(),
            'total_packages' => $packageStats->total_packages ?? 0,
            'total_weight' => number_format($packageStats->total_weight ?? 0, 2),
            'total_cbm' => number_format($packageStats->total_cbm ?? 0, 2),
        ];
    }

    /**
     * Transform container record for response
     */
    private function transformRecord(Container $container): array
    {
        // Clean text fields to prevent JSON issues
        $cleanText = function($text) {
            if (!$text) return '';
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
            $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);
            return $text;
        };

        // Get package count for this container
        $packageCount = \DB::table('container_hbl_package')
            ->where('container_id', $container->id)
            ->count();
        
        return [
            'id' => $container->id,
            'reference' => $cleanText($container->reference) ?: 'N/A',
            'cargo_type' => $cleanText($container->cargo_type) ?: 'N/A',
            'status' => $cleanText($container->status) ?: 'N/A',
            'container_number' => $cleanText($container->container_number) ?: '',
            'bl_number' => $cleanText($container->bl_number) ?: '',
            'awb_number' => $cleanText($container->awb_number) ?: '',
            'branch_name' => $cleanText($container->branch?->name) ?: 'N/A',
            'warehouse_name' => $cleanText($container->warehouse?->name) ?: 'N/A',
            'loaded_date' => $container->loading_started_at?->format('Y-m-d H:i:s'),
            'loading_ended_date' => $container->loading_ended_at?->format('Y-m-d H:i:s'),
            'unloaded_date' => $container->unloading_started_at?->format('Y-m-d H:i:s'),
            'unloading_ended_date' => $container->unloading_ended_at?->format('Y-m-d H:i:s'),
            'reached_date' => $container->reached_date?->format('Y-m-d'),
            'arrival_date' => $container->arrived_at_primary_warehouse?->format('Y-m-d H:i:s'),
            'release_date' => $container->departed_at_primary_warehouse?->format('Y-m-d H:i:s'),
            'total_packages' => $packageCount,
            'created_at' => $container->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Export Shipment report
     */
    public function export(Request $request)
    {
        $this->authorize('reports.shipment');

        $format = $request->input('format', 'xlsx');
        
        if ($format === 'pdf') {
            return $this->exportPDF($request);
        }

        $filename = 'shipment-report-' . date('Y-m-d-His');

        return Excel::download(
            new ShipmentReportExport($request),
            "{$filename}.{$format}"
        );
    }

    /**
     * Export as PDF
     */
    private function exportPDF(Request $request)
    {
        try {
            $query = Container::withoutGlobalScope(BranchScope::class)
                ->with([
                    'branch:id,name',
                    'warehouse:id,name',
                ]);

            $this->applyFilters($query, $request);

            $sortField = $request->input('sort_field', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortField, $sortOrder);

            $limit = $request->input('limit', 500);
            $limit = min($limit, 500);
            
            $containers = $query->limit($limit)->get();

            if ($containers->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No shipment records found matching the selected criteria.'
                ], 404);
            }

            $pdf = \PDF::loadView('pdf.reports.shipment-report', [
                'containers' => $containers,
                'filters' => $request->all(),
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ]);

            $filename = 'shipment-report-' . date('Y-m-d-His') . '.pdf';
            
            return $pdf->download($filename);

        } catch (\Exception $e) {
            \Log::error('Error exporting Shipment Report PDF: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }
}
