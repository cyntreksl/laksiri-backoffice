<?php

namespace App\Http\Controllers;

use App\Exports\LetterRegistrationRecordsExport;
use App\Models\Branch;
use App\Models\Container;
use App\Models\HBL;
use App\Models\Scopes\BranchScope;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class LetterRegistrationRecordsController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the Letter Registration Records report page
     */
    public function index(): Response
    {
        $this->authorize('reports.letter-registration-records');

        return Inertia::render('Reports/LetterRegistrationRecords', [
            'branches' => $this->getBranches(),
            'containers' => $this->getContainers(),
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
     * Get containers for filter
     */
    private function getContainers(): array
    {
        return Container::withoutGlobalScope(BranchScope::class)
            ->select('id', 'manifest_number', 'container_number')
            ->whereNotNull('manifest_number')
            ->orderBy('manifest_number')
            ->get()
            ->map(fn($container) => [
                'value' => $container->id,
                'label' => $container->manifest_number . ' - ' . $container->container_number,
                'manifest_number' => $container->manifest_number,
                'container_number' => $container->container_number
            ])
            ->toArray();
    }

    /**
     * Get Letter Registration Records report data with filters
     */
    public function getData(Request $request): JsonResponse
    {
        $this->authorize('reports.letter-registration-records');

        // Require container selection
        if (!$request->filled('container_id')) {
            return response()->json([
                'success' => true,
                'data' => [],
                'total' => 0,
                'stats' => [
                    'total_hbls' => 0,
                    'total_packages' => 0,
                    'total_weight' => 0,
                    'total_agents' => 0,
                ],
                'per_page' => 25,
                'current_page' => 1,
                'last_page' => 1,
            ]);
        }

        // Get HBLs with their related data
        $query = HBL::withoutGlobalScope(BranchScope::class)
            ->select([
                'hbl.id',
                'hbl.hbl_number',
                'hbl.consignee_name',
                'hbl.consignee_address',
                'hbl.branch_id',
                'hbl.created_at'
            ])
            ->with([
                'branch:id,name',
                'packages' => function ($query) {
                    $query->select('id', 'hbl_id', 'package_type', 'quantity', 'weight')
                          ->with(['containers:id,manifest_number,container_number']);
                }
            ])
            ->whereHas('packages.containers');

        // Apply filters
        $this->applyFilters($query, $request);

        // Get total count before pagination
        $totalRecords = $query->count();

        // Calculate summary statistics
        $stats = $this->calculateStats($query);

        // Apply sorting
        $sortField = $request->input('sort_field', 'hbl_number');
        $sortOrder = $request->input('sort_order', 'asc');

        $sortableFields = [
            'hbl_number' => 'hbl_number',
            'consignee_name' => 'consignee_name',
            'agent_name' => 'branches.name',
            'created_at' => 'created_at',
        ];

        if (isset($sortableFields[$sortField])) {
            if ($sortField === 'agent_name') {
                $query->join('branches', 'hbl.branch_id', '=', 'branches.id')
                      ->orderBy('branches.name', $sortOrder);
            } else {
                $query->orderBy($sortableFields[$sortField], $sortOrder);
            }
        } else {
            $query->orderBy('hbl_number', 'asc');
        }

        // Apply pagination
        $perPage = $request->input('per_page', 25);
        $page = $request->input('page', 1);
        
        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        // Transform data
        $data = $paginator->map(function ($hbl) {
            return $this->transformRecord($hbl);
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
        // Manifest number filter
        if ($request->filled('manifest_number')) {
            $query->whereHas('packages.containers', function ($q) use ($request) {
                $q->where('manifest_number', 'like', '%' . $request->input('manifest_number') . '%');
            });
        }

        // Branch filter
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        // Container filter
        if ($request->filled('container_id')) {
            $query->whereHas('packages.containers', function ($q) use ($request) {
                $q->where('containers.id', $request->input('container_id'));
            });
        }

        // General search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('hbl_number', 'like', "%{$search}%")
                    ->orWhere('consignee_name', 'like', "%{$search}%")
                    ->orWhere('consignee_address', 'like', "%{$search}%");
            });
        }
    }

    /**
     * Calculate summary statistics
     */
    private function calculateStats($query): array
    {
        $hblIds = (clone $query)->pluck('id');
        
        if ($hblIds->isEmpty()) {
            return [
                'total_hbls' => 0,
                'total_packages' => 0,
                'total_weight' => 0,
                'total_agents' => 0,
            ];
        }

        // Get package statistics
        $packageStats = DB::table('hbl_packages')
            ->whereIn('hbl_id', $hblIds)
            ->whereNull('deleted_at')
            ->selectRaw('
                COUNT(*) as total_packages,
                SUM(quantity) as total_quantity,
                SUM(weight) as total_weight
            ')
            ->first();

        // Get agent count
        $agentCount = (clone $query)->distinct('branch_id')->count('branch_id');

        return [
            'total_hbls' => $hblIds->count(),
            'total_packages' => $packageStats->total_packages ?? 0,
            'total_quantity' => $packageStats->total_quantity ?? 0,
            'total_weight' => round($packageStats->total_weight ?? 0, 2),
            'total_agents' => $agentCount,
        ];
    }

    /**
     * Transform HBL record for response
     */
    private function transformRecord(HBL $hbl): array
    {
        // Get container and manifest information
        $manifestNumber = '';
        $containerNumber = '';
        
        if ($hbl->packages->isNotEmpty()) {
            $package = $hbl->packages->first();
            if ($package->containers->isNotEmpty()) {
                $container = $package->containers->first();
                $manifestNumber = $container->manifest_number ?? '';
                $containerNumber = $container->container_number ?? '';
            }
        }

        // Get package details
        $totalPackages = $hbl->packages->sum('quantity');
        $totalWeight = $hbl->packages->sum('weight');

        return [
            'id' => $hbl->id,
            'serial_no' => '', // Will be set during display
            'hbl_no' => $hbl->hbl_number,
            'consignee_name_address' => trim(($hbl->consignee_name ?? '') . ' ' . ($hbl->consignee_address ?? '')),
            'remarks' => '', // Can be added if needed
            'packages' => $totalPackages,
            'cb' => number_format($totalWeight, 2), // CB = Cubic/Weight
            'agent_name' => $hbl->branch?->name ?? '',
            'manifest_number' => $manifestNumber,
            'container_number' => $containerNumber,
        ];
    }

    /**
     * Export Letter Registration Records report
     */
    public function export(Request $request)
    {
        $this->authorize('reports.letter-registration-records');

        // Require container selection for export
        if (!$request->filled('container_id')) {
            return response()->json([
                'success' => false,
                'message' => 'Please select a container before exporting'
            ], 400);
        }

        try {
            $format = $request->input('format', 'xlsx');
            $filename = 'letter-registration-records-' . date('Y-m-d-His');

            // Debug: Log the request parameters
            \Log::info('Letter Registration Records Export Request', [
                'format' => $format,
                'params' => $request->all()
            ]);

            // Create export instance
            $export = new LetterRegistrationRecordsExport($request);

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
            \Log::error('Letter Registration Records Export Error: ' . $e->getMessage(), [
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