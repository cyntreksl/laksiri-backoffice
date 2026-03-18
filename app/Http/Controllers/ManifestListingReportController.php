<?php

namespace App\Http\Controllers;

use App\Exports\ManifestListingReportExport;
use App\Models\Branch;
use App\Models\Container;
use App\Models\Scopes\BranchScope;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class ManifestListingReportController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the Manifest Listing report page
     */
    public function index(): Response
    {
        $this->authorize('reports.manifest-listing');

        return Inertia::render('Reports/ManifestListingReport', [
            'branches' => $this->getBranches(),
            'users' => $this->getUsers(),
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
     * Get users for filter
     */
    private function getUsers(): array
    {
        return User::select('id', 'name')
            ->whereHas('manifestGeneratedContainers', function ($query) {
                $query->whereNotNull('manifest_number');
            })
            ->orderBy('name')
            ->get()
            ->map(fn($user) => [
                'value' => $user->id,
                'label' => $user->name
            ])
            ->toArray();
    }

    /**
     * Get Manifest Listing report data with filters
     */
    public function getData(Request $request): JsonResponse
    {
        $this->authorize('reports.manifest-listing');

        $query = Container::withoutGlobalScope(BranchScope::class)
            ->select([
                'id',
                'reference',
                'manifest_number',
                'manifest_generated_at',
                'manifest_generated_by',
                'branch_id',
                'vessel_name',
                'created_at'
            ])
            ->whereNotNull('manifest_number')
            ->with([
                'branch:id,name',
                'manifestGeneratedByUser:id,name'
            ]);

        // Apply filters
        $this->applyFilters($query, $request);

        // Get total count before pagination
        $totalRecords = $query->count();

        // Calculate summary statistics
        $stats = $this->calculateStats($query);

        // Apply sorting
        $sortField = $request->input('sort_field', 'manifest_generated_at');
        $sortOrder = $request->input('sort_order', 'desc');

        $sortableFields = [
            'manifest_number' => 'manifest_number',
            'manifest_generated_at' => 'manifest_generated_at',
            'vessel_name' => 'vessel_name',
            'created_at' => 'created_at',
        ];

        $dbSortField = $sortableFields[$sortField] ?? 'manifest_generated_at';
        $query->orderBy($dbSortField, $sortOrder);

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
            $query->where('manifest_generated_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->where('manifest_generated_at', '<=', $request->input('date_to') . ' 23:59:59');
        }

        // Branch filter (Agent Name)
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        // Created User filter
        if ($request->filled('created_user_id')) {
            $query->where('manifest_generated_by', $request->input('created_user_id'));
        }

        // Manifest number range filter
        if ($request->filled('manifest_number_from')) {
            $query->where('manifest_number', '>=', $request->input('manifest_number_from'));
        }

        if ($request->filled('manifest_number_to')) {
            $query->where('manifest_number', '<=', $request->input('manifest_number_to'));
        }

        // General search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('manifest_number', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%")
                    ->orWhere('vessel_name', 'like', "%{$search}%");
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
                'total_containers' => 0,
                'total_consignees' => 0,
                'total_packages' => 0,
            ];
        }

        // Get package statistics
        $packageStats = \DB::table('container_hbl_package')
            ->whereIn('container_id', $containerIds)
            ->selectRaw('
                COUNT(*) as total_packages,
                COUNT(DISTINCT hbl_package_id) as unique_packages,
                COUNT(DISTINCT container_id) as containers_with_packages
            ')
            ->first();

        // Get consignee count
        $consigneeCount = \DB::table('container_hbl_package')
            ->join('hbl_packages', 'container_hbl_package.hbl_package_id', '=', 'hbl_packages.id')
            ->whereIn('container_hbl_package.container_id', $containerIds)
            ->whereNull('hbl_packages.deleted_at')
            ->distinct('hbl_packages.hbl_id')
            ->count('hbl_packages.hbl_id');

        return [
            'total_manifests' => $containerIds->count(),
            'total_containers' => $containerIds->count(),
            'total_consignees' => $consigneeCount,
            'total_packages' => $packageStats->total_packages ?? 0,
        ];
    }

    /**
     * Transform Container record for response
     */
    private function transformRecord(Container $container): array
    {
        // Get package and consignee counts
        $packageCount = $container->hbl_packages()->count();
        $consigneeCount = $container->hbl_packages()
            ->distinct('hbl_id')
            ->count('hbl_id');

        return [
            'id' => $container->id,
            'manifest_number' => $container->manifest_number,
            'date' => $container->manifest_generated_at?->format('d/m/Y'),
            'agent_name' => $container->branch?->name ?? '',
            'vessel_name' => $container->vessel_name ?? '',
            'no_of_consignee' => $consigneeCount,
            'no_of_packages' => $packageCount,
            'user_id' => $container->manifestGeneratedByUser?->name ?? '',
            'manifest_generated_at' => $container->manifest_generated_at?->format('Y-m-d H:i:s'),
            'created_at' => $container->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Export Manifest Listing report
     */
    public function export(Request $request)
    {
        $this->authorize('reports.manifest-listing');

        try {
            $format = $request->input('format', 'xlsx');
            $filename = 'manifest-listing-report-' . date('Y-m-d-His');

            // Debug: Log the request parameters
            \Log::info('Manifest Listing Export Request', [
                'format' => $format,
                'params' => $request->all()
            ]);

            // Test query first to ensure data exists
            $testQuery = Container::withoutGlobalScope(BranchScope::class)
                ->whereNotNull('manifest_number')
                ->count();

            \Log::info('Manifest Listing Export Data Check', [
                'total_containers_with_manifest' => $testQuery
            ]);

            if ($testQuery === 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'No manifest data found to export'
                ], 404);
            }

            // Create export instance
            $export = new ManifestListingReportExport($request);

            // Handle different formats like other reports
            switch ($format) {
                case 'pdf':
                    return Excel::download($export, $filename . '.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
                case 'csv':
                    return Excel::download($export, $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
                default:
                    return Excel::download($export, $filename . '.xlsx');
            }

        } catch (\Exception $e) {
            \Log::error('Manifest Listing Export Error: ' . $e->getMessage(), [
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