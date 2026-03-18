<?php

namespace App\Http\Controllers;

use App\Exports\UnmanifestedCargoExport;
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

class UnmanifestedCargoController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the Unmanifested Cargo report page
     */
    public function index(): Response
    {
        $this->authorize('reports.unmanifested-cargo');

        return Inertia::render('Reports/UnmanifestedCargo', [
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
     * Get Unmanifested Cargo report data with filters
     */
    public function getData(Request $request): JsonResponse
    {
        $this->authorize('reports.unmanifested-cargo');

        // Get unloaded HBLs (unmanifested cargo)
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
                    $query->select('id', 'hbl_id', 'package_type', 'quantity', 'unloaded_at')
                          ->where('is_unloaded', true);
                }
            ])
            ->whereHas('packages', function ($query) {
                $query->where('is_unloaded', true);
            });

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
        // Date range filter (unloading date)
        if ($request->filled('date_from')) {
            $query->whereHas('packages', function ($q) use ($request) {
                $q->where('is_unloaded', true)
                  ->whereDate('unloaded_at', '>=', $request->input('date_from'));
            });
        }

        if ($request->filled('date_to')) {
            $query->whereHas('packages', function ($q) use ($request) {
                $q->where('is_unloaded', true)
                  ->whereDate('unloaded_at', '<=', $request->input('date_to'));
            });
        }

        // Manifest number range filter
        if ($request->filled('manifest_number_from')) {
            $query->whereHas('packages.containers', function ($q) use ($request) {
                $q->where('manifest_number', '>=', $request->input('manifest_number_from'));
            });
        }

        if ($request->filled('manifest_number_to')) {
            $query->whereHas('packages.containers', function ($q) use ($request) {
                $q->where('manifest_number', '<=', $request->input('manifest_number_to'));
            });
        }

        // Branch filter
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        // General search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('hbl_number', 'like', "%{$search}%")
                    ->orWhere('consignee_name', 'like', "%{$search}%");
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
                'total_agents' => 0,
            ];
        }

        // Get package statistics
        $packageStats = DB::table('hbl_packages')
            ->whereIn('hbl_id', $hblIds)
            ->where('is_unloaded', true)
            ->whereNull('deleted_at')
            ->selectRaw('
                COUNT(*) as total_packages,
                SUM(quantity) as total_quantity
            ')
            ->first();

        // Get agent count
        $agentCount = (clone $query)->distinct('branch_id')->count('branch_id');

        return [
            'total_hbls' => $hblIds->count(),
            'total_packages' => $packageStats->total_packages ?? 0,
            'total_quantity' => $packageStats->total_quantity ?? 0,
            'total_agents' => $agentCount,
        ];
    }

    /**
     * Transform HBL record for response
     */
    private function transformRecord(HBL $hbl): array
    {
        // Get the manifest number from related containers
        $manifestNumber = '';
        $destuffDate = '';
        
        if ($hbl->packages->isNotEmpty()) {
            $package = $hbl->packages->first();
            $container = $package->containers()->first();
            if ($container) {
                $manifestNumber = $container->manifest_number ?? '';
            }
            $destuffDate = $package->unloaded_at ? $package->unloaded_at->format('d/m/Y') : '';
        }

        // Get package details
        $packageTypes = $hbl->packages->pluck('package_type')->unique()->implode(', ');
        $totalQuantity = $hbl->packages->sum('quantity');

        return [
            'id' => $hbl->id,
            'agent_name' => $hbl->branch?->name ?? '',
            'destuff_date' => $destuffDate,
            'hbl_no' => $hbl->hbl_number,
            'consignee_name_address' => trim(($hbl->consignee_name ?? '') . ' ' . ($hbl->consignee_address ?? '')),
            'type_of_package' => $packageTypes,
            'quantity' => $totalQuantity,
            'manifest_number' => $manifestNumber,
        ];
    }

    /**
     * Export Unmanifested Cargo report
     */
    public function export(Request $request)
    {
        $this->authorize('reports.unmanifested-cargo');

        try {
            $format = $request->input('format', 'xlsx');
            $filename = 'unmanifested-cargo-report-' . date('Y-m-d-His');

            // Debug: Log the request parameters
            \Log::info('Unmanifested Cargo Export Request', [
                'format' => $format,
                'params' => $request->all()
            ]);

            // Create export instance
            $export = new UnmanifestedCargoExport($request);

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
            \Log::error('Unmanifested Cargo Export Error: ' . $e->getMessage(), [
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