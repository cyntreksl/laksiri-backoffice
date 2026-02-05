<?php

namespace App\Http\Controllers;

use App\Actions\HBL\DownloadHBLReportPDF;
use App\Actions\HBL\StreamHBLReportPDF;
use App\Enum\CargoType;
use App\Enum\HBLType;
use App\Models\HBL;
use App\Models\Branch;
use App\Models\Container;
use App\Models\Examination;
use App\Exports\HBLReportExport;
use App\Models\Scopes\BranchScope;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class HBLReportController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the HBL report page
     */
    public function index(): Response
    {
        $this->authorize('reports.hbl');

        return Inertia::render('Reports/HBLReport', [
            'cargoTypes' => CargoType::cases(),
            'hblTypes' => HBLType::cases(),
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
            ->limit(500) // Limit to recent containers for performance
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
            ->limit(1000) // Limit for performance
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
     * Get HBL report data with filters
     */
    public function getData(Request $request): JsonResponse
    {
        $this->authorize('reports.hbl');

        $query = HBL::withoutGlobalScope(BranchScope::class)
            ->select([
                'id',
                'reference',
                'hbl_number',
                'hbl',
                'hbl_name',
                'contact_number',
                'email',
                'cargo_type',
                'hbl_type',
                'warehouse',
                'consignee_name',
                'consignee_contact',
                'consignee_address',
                'branch_id',
                'created_by',
                'is_short_loading',
                'created_at'
            ]);

        // Apply filters
        $this->applyFilters($query, $request);

        // Get total count before pagination
        $totalRecords = $query->count();

        // Calculate summary statistics (only when needed, not on every request)
        $stats = $this->calculateStats($query);

        // Apply sorting
        $sortField = $request->input('sort_field', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        $sortableFields = [
            'reference' => 'reference',
            'hbl_number' => 'hbl_number',
            'hbl_name' => 'hbl_name',
            'cargo_type' => 'cargo_type',
            'hbl_type' => 'hbl_type',
            'created_at' => 'created_at',
        ];

        $dbSortField = $sortableFields[$sortField] ?? 'created_at';
        $query->orderBy($dbSortField, $sortOrder);

        // Apply pagination
        $perPage = $request->input('per_page', 25);
        $page = $request->input('page', 1);
        
        // Use paginate instead of manual skip/take for better performance
        $paginator = $query->paginate($perPage, ['*'], 'page', $page);

        // Load only necessary relationships for the current page
        $paginator->load([
            'branch:id,name',
            'user:id,name',
            'latestDetainRecord'
        ]);

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
        // Loaded Date Range
        if ($request->filled('loaded_date_from')) {
            $query->whereHas('packages', function ($q) use ($request) {
                $q->where('loaded_at', '>=', $request->input('loaded_date_from'));
            });
        }

        if ($request->filled('loaded_date_to')) {
            $query->whereHas('packages', function ($q) use ($request) {
                $q->where('loaded_at', '<=', $request->input('loaded_date_to') . ' 23:59:59');
            });
        }

        // Unloaded/Destuff Date Range
        if ($request->filled('unloaded_date_from')) {
            $query->whereHas('packages', function ($q) use ($request) {
                $q->where('unloaded_at', '>=', $request->input('unloaded_date_from'));
            });
        }

        if ($request->filled('unloaded_date_to')) {
            $query->whereHas('packages', function ($q) use ($request) {
                $q->where('unloaded_at', '<=', $request->input('unloaded_date_to') . ' 23:59:59');
            });
        }

        // Branch/Agent filter
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->input('branch_id'));
        }

        // Customer search (HBL name, contact, email)
        if ($request->filled('customer_search')) {
            $search = $request->input('customer_search');
            $query->where('hbl_name', $search);
        }

        // Appointment Date Range
        if ($request->filled('appointment_date_from')) {
            $query->whereHas('callFlags', function ($q) use ($request) {
                $q->where('appointment_date', '>=', $request->input('appointment_date_from'));
            });
        }

        if ($request->filled('appointment_date_to')) {
            $query->whereHas('callFlags', function ($q) use ($request) {
                $q->where('appointment_date', '<=', $request->input('appointment_date_to') . ' 23:59:59');
            });
        }

        // Token Issued Date Range
        if ($request->filled('token_issued_date_from')) {
            $query->whereHas('tokens', function ($q) use ($request) {
                $q->where('created_at', '>=', $request->input('token_issued_date_from'));
            });
        }

        if ($request->filled('token_issued_date_to')) {
            $query->whereHas('tokens', function ($q) use ($request) {
                $q->where('created_at', '<=', $request->input('token_issued_date_to') . ' 23:59:59');
            });
        }

        // Gate Pass Marked Date Range
        if ($request->filled('gate_pass_date_from')) {
            $query->whereHas('tokens', function ($q) use ($request) {
                $q->whereHas('examination', function ($subQ) use ($request) {
                    $subQ->where('is_issued_gate_pass', true)
                        ->where('released_at', '>=', $request->input('gate_pass_date_from'));
                });
            });
        }

        if ($request->filled('gate_pass_date_to')) {
            $query->whereHas('tokens', function ($q) use ($request) {
                $q->whereHas('examination', function ($subQ) use ($request) {
                    $subQ->where('is_issued_gate_pass', true)
                        ->where('released_at', '<=', $request->input('gate_pass_date_to') . ' 23:59:59');
                });
            });
        }

        // Cashier Invoice Date Range
        if ($request->filled('cashier_invoice_date_from')) {
            $query->whereHas('tokens', function ($q) use ($request) {
                $q->whereHas('cashierPayment', function ($subQ) use ($request) {
                    $subQ->where('created_at', '>=', $request->input('cashier_invoice_date_from'));
                });
            });
        }

        if ($request->filled('cashier_invoice_date_to')) {
            $query->whereHas('tokens', function ($q) use ($request) {
                $q->whereHas('cashierPayment', function ($subQ) use ($request) {
                    $subQ->where('created_at', '<=', $request->input('cashier_invoice_date_to') . ' 23:59:59');
                });
            });
        }

        // Document Verified Date Range
        if ($request->filled('document_verified_date_from')) {
            $query->whereHas('tokens.verification', function ($q) use ($request) {
                $q->where('created_at', '>=', $request->input('document_verified_date_from'));
            });
        }

        if ($request->filled('document_verified_date_to')) {
            $query->whereHas('tokens.verification', function ($q) use ($request) {
                $q->where('created_at', '<=', $request->input('document_verified_date_to') . ' 23:59:59');
            });
        }

        // Shipment/Container filter
        if ($request->filled('container_id')) {
            $query->whereHas('packages.containers', function ($q) use ($request) {
                $q->where('containers.id', $request->input('container_id'));
            });
        }

        // Cargo Type filter
        if ($request->filled('cargo_type')) {
            $query->where('cargo_type', $request->input('cargo_type'));
        }

        // HBL Type filter
        if ($request->filled('hbl_type')) {
            $query->where('hbl_type', $request->input('hbl_type'));
        }

        // General search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('hbl_number', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%")
                    ->orWhere('hbl_name', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%");
            });
        }
    }

    /**
     * Calculate summary statistics
     */
    private function calculateStats($query): array
    {
        // Get HBL IDs from filtered query first
        $hblIds = (clone $query)->pluck('id');
        
        if ($hblIds->isEmpty()) {
            return [
                'total_hbls' => 0,
                'total_amount' => '0.00',
                'total_paid' => '0.00',
                'total_packages' => 0,
            ];
        }

        // Calculate aggregates using a fresh query on the IDs
        $stats = HBL::withoutGlobalScope(BranchScope::class)
            ->whereIn('id', $hblIds)
            ->selectRaw('
                COUNT(*) as total_hbls,
                COALESCE(SUM(grand_total), 0) as total_amount,
                COALESCE(SUM(paid_amount), 0) as total_paid
            ')
            ->first();

        // Get total packages count
        $totalPackages = \DB::table('hbl_packages')
            ->whereIn('hbl_id', $hblIds)
            ->whereNull('deleted_at')
            ->count();

        return [
            'total_hbls' => $stats->total_hbls ?? 0,
            'total_amount' => number_format($stats->total_amount ?? 0, 2),
            'total_paid' => number_format($stats->total_paid ?? 0, 2),
            'total_packages' => $totalPackages,
        ];
    }

    /**
     * Transform HBL record for response
     */
    private function transformRecord(HBL $hbl): array
    {
        return [
            'id' => $hbl->id,
            'reference' => $hbl->reference,
            'hbl_number' => $hbl->hbl_number,
            'hbl' => $hbl->hbl,
            'hbl_name' => $hbl->hbl_name,
            'contact_number' => $hbl->contact_number,
            'email' => $hbl->email,
            'cargo_type' => $hbl->cargo_type,
            'hbl_type' => $hbl->hbl_type,
            'warehouse' => $hbl->warehouse,
            'consignee_name' => $hbl->consignee_name,
            'consignee_contact' => $hbl->consignee_contact,
            'consignee_address' => $hbl->consignee_address,
            'is_rtf' => $hbl->latestDetainRecord?->is_rtf ?? false,
            'is_short_loaded' => $hbl->is_short_loading ?? false,
            'branch' => $hbl->branch ? [
                'id' => $hbl->branch->id,
                'name' => $hbl->branch->name,
            ] : null,
            'latest_detain_record' => $hbl->latestDetainRecord ? [
                'is_rtf' => $hbl->latestDetainRecord->is_rtf,
                'detain_type' => $hbl->latestDetainRecord->detain_type,
            ] : null,
            'total_packages' => $hbl->packages()->count(),
            'created_at' => $hbl->created_at?->format('Y-m-d H:i:s'),
            'created_by' => $hbl->user ? [
                'id' => $hbl->user->id,
                'name' => $hbl->user->name,
            ] : null,
        ];
    }

    /**
     * Export HBL report
     */
    public function export(Request $request)
    {
        $this->authorize('reports.hbl');

        $format = $request->input('format', 'xlsx');
        
        // For PDF format, use the Lambda-style action with Blade template
        if ($format === 'pdf') {
            return $this->exportPDF($request);
        }

        // For Excel/CSV formats, use the existing export class
        $filename = 'hbl-report-' . date('Y-m-d-His');

        return Excel::download(
            new HBLReportExport($request),
            "{$filename}.{$format}"
        );
    }

    /**
     * Export HBL report as PDF using BrowsershotLambda
     */
    private function exportPDF(Request $request)
    {
        // Build query with same filters as getData method
        $query = HBL::withoutGlobalScope(BranchScope::class)
            ->with([
                'branch:id,name',
                'user:id,name',
                'packages:id,hbl_id', // Only load necessary package fields
                'latestDetainRecord' => function ($query) {
                    // Specify table name to avoid ambiguous column error
                    $query->select('detain_records.id', 'detain_records.rtfable_id', 'detain_records.rtfable_type', 'detain_records.is_rtf', 'detain_records.detain_type');
                }
            ]);

        // Apply filters
        $this->applyFilters($query, $request);

        // Apply sorting
        $sortField = $request->input('sort_field', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');

        $sortableFields = [
            'reference' => 'reference',
            'hbl_number' => 'hbl_number',
            'hbl_name' => 'hbl_name',
            'cargo_type' => 'cargo_type',
            'hbl_type' => 'hbl_type',
            'created_at' => 'created_at',
        ];

        $dbSortField = $sortableFields[$sortField] ?? 'created_at';
        $query->orderBy($dbSortField, $sortOrder);

        // Limit records for PDF performance (reduce from 1000 to 500 for faster generation)
        $limit = $request->input('limit', 500);
        $limit = min($limit, 500); // Cap at 500 records
        
        $hbls = $query->limit($limit)->get();

        // Check if no records found
        if ($hbls->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No HBL records found matching the selected criteria.'
            ], 404);
        }

        // Check if user wants to stream (inline) or download
        $stream = $request->input('stream', false);

        // Use detailed template only if explicitly requested and record count is low
        $detailed = $request->input('detailed', false) && $hbls->count() <= 100;

        if ($stream) {
            // Use streaming action for inline display
            return StreamHBLReportPDF::run($hbls, $request, $detailed);
        }

        // Use download action for file download
        return DownloadHBLReportPDF::run($hbls, $request, $detailed);
    }
}
