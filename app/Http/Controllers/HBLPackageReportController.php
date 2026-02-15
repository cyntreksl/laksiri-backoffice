<?php

namespace App\Http\Controllers;

use App\Enum\CargoType;
use App\Models\HBLPackage;
use App\Models\Branch;
use App\Models\Container;
use App\Models\HBL;
use App\Exports\HBLPackageReportExport;
use App\Models\Scopes\BranchScope;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class HBLPackageReportController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the HBL Package report page
     */
    public function index(): Response
    {
        $this->authorize('reports.hbl-package');

        return Inertia::render('Reports/HBLPackageReport', [
            'cargoTypes' => CargoType::cases(),
            'branches' => $this->getBranches(),
            'containers' => $this->getContainers(),
            'customers' => $this->getCustomers(),
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
     * Get containers for filter
     */
    private function getContainers(): array
    {
        return Container::withoutGlobalScope(BranchScope::class)
            ->select('id', 'reference')
            ->orderBy('reference', 'desc')
            ->limit(500)
            ->get()
            ->map(fn($container) => [
                'id' => $container->id,
                'reference' => $container->reference
            ])
            ->toArray();
    }

    /**
     * Get customers for filter
     */
    private function getCustomers(): array
    {
        return HBL::withoutGlobalScope(BranchScope::class)
            ->select('hbl_name', 'contact_number', 'email')
            ->whereNotNull('hbl_name')
            ->where('hbl_name', '!=', '')
            ->groupBy('hbl_name', 'contact_number', 'email')
            ->orderBy('hbl_name')
            ->limit(1000)
            ->get()
            ->map(fn($hbl) => [
                'label' => $hbl->hbl_name . ($hbl->contact_number ? ' - ' . $hbl->contact_number : ''),
                'value' => $hbl->hbl_name,
                'contact' => $hbl->contact_number,
                'email' => $hbl->email,
            ])
            ->toArray();
    }

    /**
     * Get HBL Package report data with filters
     */
    public function getData(Request $request): JsonResponse
    {
        try {
            $this->authorize('reports.hbl-package');

            // Check if we should group by HBL
            $groupByHBL = $request->boolean('group_by_hbl', false);

            if ($groupByHBL) {
                return $this->getGroupedByHBL($request);
            }

            // Original package-level data
            $query = HBLPackage::withoutGlobalScope(BranchScope::class)
                ->select([
                    'hbl_packages.id',
                    'hbl_packages.hbl_id',
                    'hbl_packages.package_type',
                    'hbl_packages.weight',
                    'hbl_packages.volume',
                    'hbl_packages.remarks',
                    'hbl_packages.loaded_at',
                    'hbl_packages.unloaded_at',
                    'hbl_packages.created_at',
                ])
                ->with([
                    'hbl:id,hbl_number,hbl_name,contact_number,email,cargo_type,branch_id',
                    'hbl.branch:id,name',
                    'containers:id,reference'
                ]);

            // Apply filters
            $this->applyFilters($query, $request);

            // Get total count before pagination
            $totalRecords = $query->count();

            // Calculate summary statistics
            $stats = $this->calculateStats($query);

            // Apply sorting
            $sortField = $request->input('sort_field', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');

            $sortableFields = [
                'hbl_number' => 'hbl_number',
                'package_number' => 'id',
                'cargo_type' => 'cargo_type',
                'weight' => 'weight',
                'cbm' => 'volume',
                'loaded_date' => 'loaded_at',
                'unloaded_date' => 'unloaded_at',
                'created_at' => 'hbl_packages.created_at',
            ];

            $dbSortField = $sortableFields[$sortField] ?? 'hbl_packages.created_at';
            $query->orderBy($dbSortField, $sortOrder);

            // Apply pagination
            $perPage = $request->input('per_page', 25);
            $page = $request->input('page', 1);
            
            $paginator = $query->paginate($perPage, ['*'], 'page', $page);

            // Transform data
            $data = $paginator->map(function ($package) {
                try {
                    return $this->transformRecord($package);
                } catch (\Exception $e) {
                    \Log::error('Error transforming package record: ' . $e->getMessage(), [
                        'package_id' => $package->id ?? null
                    ]);
                    return null;
                }
            })->filter(); // Remove null values

            return response()->json([
                'success' => true,
                'data' => $data->values(), // Re-index array after filtering
                'total' => $paginator->total(),
                'stats' => $stats,
                'per_page' => $paginator->perPage(),
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
            ], 200, [], JSON_UNESCAPED_UNICODE);
            
        } catch (\Exception $e) {
            \Log::error('Error in HBL Package Report getData: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load package report data',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get data grouped by HBL
     */
    private function getGroupedByHBL(Request $request): JsonResponse
    {
        $query = HBL::withoutGlobalScope(BranchScope::class)
            ->select([
                'hbl.id',
                'hbl.hbl_number',
                'hbl.hbl_name',
                'hbl.contact_number',
                'hbl.email',
                'hbl.cargo_type',
                'hbl.branch_id',
            ]);

        // Apply filters (modified for HBL level)
        $this->applyHBLFilters($query, $request);

        // Get total count
        $totalRecords = $query->count();

        // Calculate stats
        $stats = $this->calculateHBLStats($query);

        // Apply sorting
        $sortField = $request->input('sort_field', 'hbl_number');
        $sortOrder = $request->input('sort_order', 'desc');

        $sortableFields = [
            'hbl_number' => 'hbl_number',
            'cargo_type' => 'cargo_type',
            'customer_name' => 'hbl_name',
        ];

        $dbSortField = $sortableFields[$sortField] ?? 'hbl_number';
        $query->orderBy($dbSortField, $sortOrder);

        // Pagination
        $perPage = $request->input('per_page', 25);
        $page = $request->input('page', 1);
        
        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        // Load relationships after pagination
        $paginator->load(['branch:id,name']);

        // Transform data - calculate package stats per HBL
        $data = $paginator->map(function ($hbl) {
            // Get package stats for this HBL
            $packageStats = HBLPackage::withoutGlobalScope(BranchScope::class)
                ->where('hbl_id', $hbl->id)
                ->selectRaw('
                    COUNT(*) as total_packages,
                    COALESCE(SUM(weight), 0) as total_weight,
                    COALESCE(SUM(volume), 0) as total_cbm
                ')
                ->first();

            // Get first container reference
            $container = HBLPackage::withoutGlobalScope(BranchScope::class)
                ->where('hbl_id', $hbl->id)
                ->with('containers:id,reference')
                ->first()?->containers->first();
            
            return [
                'hbl_id' => $hbl->id,
                'hbl_number' => $hbl->hbl_number ?? 'N/A',
                'cargo_type' => $hbl->cargo_type ?? 'N/A',
                'customer_name' => $hbl->hbl_name ?? 'N/A',
                'customer_contact' => $hbl->contact_number ?? '',
                'customer_email' => $hbl->email ?? '',
                'branch_name' => $hbl->branch?->name ?? 'N/A',
                'total_packages' => $packageStats->total_packages ?? 0,
                'total_weight' => (float) ($packageStats->total_weight ?? 0),
                'total_cbm' => (float) ($packageStats->total_cbm ?? 0),
                'container_reference' => $container?->reference ?? null,
            ];
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
    }

    /**
     * Apply filters for HBL-level query
     */
    private function applyHBLFilters($query, Request $request): void
    {
        // Filter HBLs that have packages matching the criteria
        if ($request->filled('loaded_date_from') || $request->filled('loaded_date_to')) {
            $query->whereHas('packages', function ($q) use ($request) {
                if ($request->filled('loaded_date_from')) {
                    $q->where('loaded_at', '>=', $request->input('loaded_date_from'));
                }
                if ($request->filled('loaded_date_to')) {
                    $q->where('loaded_at', '<=', $request->input('loaded_date_to') . ' 23:59:59');
                }
            });
        }

        if ($request->filled('unloaded_date_from') || $request->filled('unloaded_date_to')) {
            $query->whereHas('packages', function ($q) use ($request) {
                if ($request->filled('unloaded_date_from')) {
                    $q->where('unloaded_at', '>=', $request->input('unloaded_date_from'));
                }
                if ($request->filled('unloaded_date_to')) {
                    $q->where('unloaded_at', '<=', $request->input('unloaded_date_to') . ' 23:59:59');
                }
            });
        }

        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        if ($request->filled('customer_search')) {
            $query->where('hbl_name', $request->input('customer_search'));
        }

        if ($request->filled('cargo_type')) {
            $query->where('cargo_type', $request->input('cargo_type'));
        }

        if ($request->filled('container_id')) {
            $query->whereHas('packages.containers', function ($q) use ($request) {
                $q->where('containers.id', $request->input('container_id'));
            });
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('hbl_number', 'like', "%{$search}%")
                    ->orWhere('hbl_name', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%");
            });
        }
    }

    /**
     * Calculate stats for HBL-level view
     */
    private function calculateHBLStats($query): array
    {
        $hblIds = (clone $query)->pluck('id');
        
        if ($hblIds->isEmpty()) {
            return [
                'total_packages' => 0,
                'total_weight' => '0.00',
                'total_cbm' => '0.00',
                'total_hbls' => 0,
            ];
        }

        $stats = HBLPackage::withoutGlobalScope(BranchScope::class)
            ->whereIn('hbl_id', $hblIds)
            ->selectRaw('
                COUNT(*) as total_packages,
                COALESCE(SUM(weight), 0) as total_weight,
                COALESCE(SUM(volume), 0) as total_cbm,
                COUNT(DISTINCT hbl_id) as total_hbls
            ')
            ->first();

        return [
            'total_packages' => $stats->total_packages ?? 0,
            'total_weight' => number_format($stats->total_weight ?? 0, 2),
            'total_cbm' => number_format($stats->total_cbm ?? 0, 2),
            'total_hbls' => $stats->total_hbls ?? 0,
        ];
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, Request $request): void
    {
        // Loaded Date Range
        if ($request->filled('loaded_date_from')) {
            $query->where('hbl_packages.loaded_at', '>=', $request->input('loaded_date_from'));
        }

        if ($request->filled('loaded_date_to')) {
            $query->where('hbl_packages.loaded_at', '<=', $request->input('loaded_date_to') . ' 23:59:59');
        }

        // Unloaded/Destuff Date Range
        if ($request->filled('unloaded_date_from')) {
            $query->where('hbl_packages.unloaded_at', '>=', $request->input('unloaded_date_from'));
        }

        if ($request->filled('unloaded_date_to')) {
            $query->where('hbl_packages.unloaded_at', '<=', $request->input('unloaded_date_to') . ' 23:59:59');
        }

        // Branch/Agent filter
        if ($request->filled('branch_id')) {
            $query->whereHas('hbl', function ($q) use ($request) {
                $q->where('branch_id', $request->input('branch_id'));
            });
        }

        // Customer search
        if ($request->filled('customer_search')) {
            $search = $request->input('customer_search');
            $query->whereHas('hbl', function ($q) use ($search) {
                $q->where('hbl_name', $search);
            });
        }

        // Appointment Date Range
        if ($request->filled('appointment_date_from')) {
            $query->whereHas('hbl.callFlags', function ($q) use ($request) {
                $q->where('appointment_date', '>=', $request->input('appointment_date_from'));
            });
        }

        if ($request->filled('appointment_date_to')) {
            $query->whereHas('hbl.callFlags', function ($q) use ($request) {
                $q->where('appointment_date', '<=', $request->input('appointment_date_to') . ' 23:59:59');
            });
        }

        // Token Issued Date Range
        if ($request->filled('token_issued_date_from')) {
            $query->whereHas('hbl.tokens', function ($q) use ($request) {
                $q->where('created_at', '>=', $request->input('token_issued_date_from'));
            });
        }

        if ($request->filled('token_issued_date_to')) {
            $query->whereHas('hbl.tokens', function ($q) use ($request) {
                $q->where('created_at', '<=', $request->input('token_issued_date_to') . ' 23:59:59');
            });
        }

        // Gate Pass Marked Date Range
        if ($request->filled('gate_pass_date_from')) {
            $query->whereHas('hbl.tokens', function ($q) use ($request) {
                $q->whereHas('examination', function ($subQ) use ($request) {
                    $subQ->where('is_issued_gate_pass', true)
                        ->where('released_at', '>=', $request->input('gate_pass_date_from'));
                });
            });
        }

        if ($request->filled('gate_pass_date_to')) {
            $query->whereHas('hbl.tokens', function ($q) use ($request) {
                $q->whereHas('examination', function ($subQ) use ($request) {
                    $subQ->where('is_issued_gate_pass', true)
                        ->where('released_at', '<=', $request->input('gate_pass_date_to') . ' 23:59:59');
                });
            });
        }

        // Cashier Invoice Date Range
        if ($request->filled('cashier_invoice_date_from')) {
            $query->whereHas('hbl.tokens', function ($q) use ($request) {
                $q->whereHas('cashierPayment', function ($subQ) use ($request) {
                    $subQ->where('created_at', '>=', $request->input('cashier_invoice_date_from'));
                });
            });
        }

        if ($request->filled('cashier_invoice_date_to')) {
            $query->whereHas('hbl.tokens', function ($q) use ($request) {
                $q->whereHas('cashierPayment', function ($subQ) use ($request) {
                    $subQ->where('created_at', '<=', $request->input('cashier_invoice_date_to') . ' 23:59:59');
                });
            });
        }

        // Document Verified Date Range
        if ($request->filled('document_verified_date_from')) {
            $query->whereHas('hbl.tokens.verification', function ($q) use ($request) {
                $q->where('created_at', '>=', $request->input('document_verified_date_from'));
            });
        }

        if ($request->filled('document_verified_date_to')) {
            $query->whereHas('hbl.tokens.verification', function ($q) use ($request) {
                $q->where('created_at', '<=', $request->input('document_verified_date_to') . ' 23:59:59');
            });
        }

        // Shipment/Container filter
        if ($request->filled('container_id')) {
            $query->whereHas('containers', function ($q) use ($request) {
                $q->where('containers.id', $request->input('container_id'));
            });
        }

        // Cargo Type filter
        if ($request->filled('cargo_type')) {
            $query->whereHas('hbl', function ($q) use ($request) {
                $q->where('cargo_type', $request->input('cargo_type'));
            });
        }

        // General search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('hbl_packages.id', 'like', "%{$search}%")
                    ->orWhere('hbl_packages.remarks', 'like', "%{$search}%")
                    ->orWhereHas('hbl', function ($subQ) use ($search) {
                        $subQ->where('hbl_number', 'like', "%{$search}%")
                            ->orWhere('hbl_name', 'like', "%{$search}%")
                            ->orWhere('contact_number', 'like', "%{$search}%");
                    });
            });
        }
    }

    /**
     * Calculate summary statistics
     */
    private function calculateStats($query): array
    {
        $packageIds = (clone $query)->pluck('hbl_packages.id');
        
        if ($packageIds->isEmpty()) {
            return [
                'total_packages' => 0,
                'total_weight' => '0.00',
                'total_cbm' => '0.00',
                'total_hbls' => 0,
            ];
        }

        $stats = HBLPackage::withoutGlobalScope(BranchScope::class)
            ->whereIn('id', $packageIds)
            ->selectRaw('
                COUNT(*) as total_packages,
                COALESCE(SUM(weight), 0) as total_weight,
                COALESCE(SUM(volume), 0) as total_cbm,
                COUNT(DISTINCT hbl_id) as total_hbls
            ')
            ->first();

        return [
            'total_packages' => $stats->total_packages ?? 0,
            'total_weight' => number_format($stats->total_weight ?? 0, 2),
            'total_cbm' => number_format($stats->total_cbm ?? 0, 2),
            'total_hbls' => $stats->total_hbls ?? 0,
        ];
    }

    /**
     * Transform package record for response
     */
    private function transformRecord(HBLPackage $package): array
    {
        // Safely get first container
        $container = $package->containers->first();
        
        // Clean text fields to prevent JSON issues
        $cleanText = function($text) {
            if (!$text) return '';
            // Remove invalid UTF-8 characters
            $text = mb_convert_encoding($text, 'UTF-8', 'UTF-8');
            // Remove control characters except newlines and tabs
            $text = preg_replace('/[\x00-\x08\x0B\x0C\x0E-\x1F\x7F]/u', '', $text);
            return $text;
        };
        
        return [
            'id' => $package->id,
            'hbl_id' => $package->hbl_id,
            'hbl_number' => $cleanText($package->hbl?->hbl_number) ?: 'N/A',
            'package_number' => 'PKG-' . str_pad($package->id, 6, '0', STR_PAD_LEFT),
            'cargo_type' => $cleanText($package->hbl?->cargo_type) ?: 'N/A',
            'customer_name' => $cleanText($package->hbl?->hbl_name) ?: 'N/A',
            'customer_contact' => $cleanText($package->hbl?->contact_number) ?: '',
            'customer_email' => $cleanText($package->hbl?->email) ?: '',
            'description' => $cleanText($package->remarks),
            'weight' => (float) ($package->weight ?? 0),
            'cbm' => (float) ($package->volume ?? 0),
            'loaded_date' => $package->loaded_at?->format('Y-m-d H:i:s'),
            'unloaded_date' => $package->unloaded_at?->format('Y-m-d H:i:s'),
            'container_reference' => $container ? $cleanText($container->reference) : null,
            'branch_name' => $cleanText($package->hbl?->branch?->name) ?: 'N/A',
            'created_at' => $package->created_at?->format('Y-m-d H:i:s'),
        ];
    }

    /**
     * Export HBL Package report
     */
    public function export(Request $request)
    {
        $this->authorize('reports.hbl-package');

        $format = $request->input('format', 'xlsx');
        
        // For PDF format, use a simple PDF generation
        if ($format === 'pdf') {
            return $this->exportPDF($request);
        }

        // For Excel/CSV formats
        $filename = 'hbl-package-report-' . date('Y-m-d-His');

        return Excel::download(
            new HBLPackageReportExport($request),
            "{$filename}.{$format}"
        );
    }

    /**
     * Export as PDF
     */
    private function exportPDF(Request $request)
    {
        try {
            // Check if exporting for specific HBL
            $hblId = $request->input('hbl_id');
            
            if ($hblId) {
                return $this->exportHBLPackagesPDF($request, $hblId);
            }

            // Export all packages
            $query = HBLPackage::withoutGlobalScope(BranchScope::class)
                ->with([
                    'hbl:id,hbl_number,hbl_name,contact_number,email,cargo_type,branch_id',
                    'hbl.branch:id,name',
                    'containers:id,reference',
                ]);

            $this->applyFilters($query, $request);

            $sortField = $request->input('sort_field', 'created_at');
            $sortOrder = $request->input('sort_order', 'desc');
            $query->orderBy($sortField, $sortOrder);

            $limit = $request->input('limit', 500);
            $limit = min($limit, 500);
            
            $packages = $query->limit($limit)->get();

            if ($packages->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No package records found matching the selected criteria.'
                ], 404);
            }

            // Generate PDF using DomPDF
            $pdf = \PDF::loadView('pdf.reports.hbl-package-report', [
                'packages' => $packages,
                'filters' => $request->all(),
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ]);

            $filename = 'hbl-package-report-' . date('Y-m-d-His') . '.pdf';
            
            return $pdf->download($filename);

        } catch (\Exception $e) {
            \Log::error('Error exporting HBL Package Report PDF: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Export packages for specific HBL as PDF
     */
    private function exportHBLPackagesPDF(Request $request, $hblId)
    {
        try {
            $hbl = HBL::withoutGlobalScope(BranchScope::class)
                ->with(['branch:id,name'])
                ->findOrFail($hblId);

            $query = HBLPackage::withoutGlobalScope(BranchScope::class)
                ->where('hbl_id', $hblId)
                ->with(['containers:id,reference']);

            // Apply date filters only if provided
            if ($request->filled('loaded_date_from')) {
                $query->where('loaded_at', '>=', $request->input('loaded_date_from'));
            }
            if ($request->filled('loaded_date_to')) {
                $query->where('loaded_at', '<=', $request->input('loaded_date_to') . ' 23:59:59');
            }
            if ($request->filled('unloaded_date_from')) {
                $query->where('unloaded_at', '>=', $request->input('unloaded_date_from'));
            }
            if ($request->filled('unloaded_date_to')) {
                $query->where('unloaded_at', '<=', $request->input('unloaded_date_to') . ' 23:59:59');
            }

            // Apply search filter
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('hbl_packages.id', 'like', "%{$search}%")
                        ->orWhere('hbl_packages.remarks', 'like', "%{$search}%");
                });
            }

            $sortField = $request->input('sort_field', 'id');
            $sortOrder = $request->input('sort_order', 'asc');
            $query->orderBy($sortField, $sortOrder);

            $packages = $query->get();

            if ($packages->isEmpty()) {
                // Log for debugging
                \Log::warning('No packages found for HBL export', [
                    'hbl_id' => $hblId,
                    'filters' => $request->all()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'No packages found for this HBL with the applied filters.'
                ], 404);
            }

            $pdf = \PDF::loadView('pdf.reports.hbl-package-detail', [
                'hbl' => $hbl,
                'packages' => $packages,
                'filters' => $request->all(),
                'generated_at' => now()->format('Y-m-d H:i:s'),
            ]);

            $filename = 'hbl-' . $hbl->hbl_number . '-packages-' . date('Y-m-d-His') . '.pdf';
            
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            \Log::error('Error exporting HBL packages PDF: ' . $e->getMessage(), [
                'hbl_id' => $hblId,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate PDF',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }

    /**
     * Get HBL details with packages for modal
     */
    public function getHBLDetails(Request $request, $hblId): JsonResponse
    {
        try {
            $this->authorize('reports.hbl-package');

            // Get HBL data
            $hbl = HBL::withoutGlobalScope(BranchScope::class)
                ->with(['branch:id,name'])
                ->findOrFail($hblId);

            // Get packages for this HBL
            $query = HBLPackage::withoutGlobalScope(BranchScope::class)
                ->where('hbl_id', $hblId)
                ->with(['containers:id,reference']);

            // Apply date filters if provided
            if ($request->filled('loaded_date_from')) {
                $query->where('loaded_at', '>=', $request->input('loaded_date_from'));
            }
            if ($request->filled('loaded_date_to')) {
                $query->where('loaded_at', '<=', $request->input('loaded_date_to') . ' 23:59:59');
            }
            if ($request->filled('unloaded_date_from')) {
                $query->where('unloaded_at', '>=', $request->input('unloaded_date_from'));
            }
            if ($request->filled('unloaded_date_to')) {
                $query->where('unloaded_at', '<=', $request->input('unloaded_date_to') . ' 23:59:59');
            }

            // Search filter
            if ($request->filled('search')) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('id', 'like', "%{$search}%")
                        ->orWhere('remarks', 'like', "%{$search}%");
                });
            }

            // Calculate stats for this HBL
            $stats = [
                'total_packages' => $query->count(),
                'total_weight' => number_format($query->sum('weight'), 2),
                'total_cbm' => number_format($query->sum('volume'), 2),
            ];

            // Apply sorting
            $sortField = $request->input('sort_field', 'id');
            $sortOrder = $request->input('sort_order', 'asc');
            $query->orderBy($sortField, $sortOrder);

            // Paginate
            $perPage = $request->input('per_page', 10);
            $paginator = $query->paginate($perPage);

            // Transform packages
            $packages = $paginator->map(function ($package) {
                return $this->transformRecord($package);
            });

            return response()->json([
                'success' => true,
                'hbl' => [
                    'id' => $hbl->id,
                    'hbl_number' => $hbl->hbl_number,
                    'hbl_name' => $hbl->hbl_name,
                    'contact_number' => $hbl->contact_number,
                    'email' => $hbl->email,
                    'cargo_type' => $hbl->cargo_type,
                    'branch' => [
                        'id' => $hbl->branch?->id,
                        'name' => $hbl->branch?->name,
                    ],
                ],
                'packages' => [
                    'data' => $packages,
                    'total' => $paginator->total(),
                    'per_page' => $paginator->perPage(),
                    'current_page' => $paginator->currentPage(),
                    'last_page' => $paginator->lastPage(),
                ],
                'stats' => $stats,
            ], 200, [], JSON_UNESCAPED_UNICODE);

        } catch (\Exception $e) {
            \Log::error('Error getting HBL details: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to load HBL details',
                'error' => config('app.debug') ? $e->getMessage() : 'Internal server error',
            ], 500);
        }
    }
}
