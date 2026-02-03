<?php

namespace App\Http\Controllers;

use App\Models\HBL;
use App\Models\Branch;
use App\Models\Container;
use App\Exports\HBLReportExport;
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
            'cargoTypes' => $this->getCargoTypes(),
            'hblTypes' => $this->getHBLTypes(),
            'branches' => $this->getBranches(),
        ]);
    }

    /**
     * Get available cargo types
     */
    private function getCargoTypes(): array
    {
        return [
            ['value' => 'General', 'label' => 'General'],
            ['value' => 'Dangerous', 'label' => 'Dangerous'],
            ['value' => 'Perishable', 'label' => 'Perishable'],
        ];
    }

    /**
     * Get available HBL types
     */
    private function getHBLTypes(): array
    {
        return [
            ['value' => 'Normal', 'label' => 'Normal'],
            ['value' => 'Door to Door', 'label' => 'Door to Door'],
            ['value' => 'Third Party', 'label' => 'Third Party'],
        ];
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
     * Get HBL report data with filters
     */
    public function getData(Request $request): JsonResponse
    {
        $this->authorize('reports.hbl');

        $query = HBL::withoutGlobalScope(\App\Models\Scopes\BranchScope::class)
            ->with([
                'branch',
                'container',
                'createdBy',
                'packages',
                'token',
                'verification',
                'cashierPayments',
                'deliver'
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
            'reference' => 'reference',
            'hbl_name' => 'hbl_name',
            'cargo_type' => 'cargo_type',
            'hbl_type' => 'hbl_type',
            'created_at' => 'created_at',
            'grand_total' => 'grand_total',
        ];
        
        $dbSortField = $sortableFields[$sortField] ?? 'created_at';
        $query->orderBy($dbSortField, $sortOrder);

        // Apply pagination
        $perPage = $request->input('per_page', 25);
        $page = $request->input('page', 1);
        $records = $query->skip(($page - 1) * $perPage)->take($perPage)->get();

        // Transform data
        $data = $records->map(function ($hbl) {
            return $this->transformRecord($hbl);
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $totalRecords,
            'stats' => $stats,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => ceil($totalRecords / $perPage),
        ]);
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, Request $request): void
    {
        // Loaded Date Range
        if ($request->filled('loaded_date_from')) {
            $query->whereHas('packages.containerPackages', function ($q) use ($request) {
                $q->where('loaded_at', '>=', $request->input('loaded_date_from'));
            });
        }

        if ($request->filled('loaded_date_to')) {
            $query->whereHas('packages.containerPackages', function ($q) use ($request) {
                $q->where('loaded_at', '<=', $request->input('loaded_date_to') . ' 23:59:59');
            });
        }

        // Unloaded/Destuff Date Range
        if ($request->filled('unloaded_date_from')) {
            $query->whereHas('packages.containerPackages', function ($q) use ($request) {
                $q->where('unloaded_at', '>=', $request->input('unloaded_date_from'));
            });
        }

        if ($request->filled('unloaded_date_to')) {
            $query->whereHas('packages.containerPackages', function ($q) use ($request) {
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
            $query->where(function ($q) use ($search) {
                $q->where('hbl_name', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('reference', 'like', "%{$search}%");
            });
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
            $query->whereHas('token', function ($q) use ($request) {
                $q->where('created_at', '>=', $request->input('token_issued_date_from'));
            });
        }

        if ($request->filled('token_issued_date_to')) {
            $query->whereHas('token', function ($q) use ($request) {
                $q->where('created_at', '<=', $request->input('token_issued_date_to') . ' 23:59:59');
            });
        }

        // Gate Pass Marked Date Range
        if ($request->filled('gate_pass_date_from')) {
            $query->whereHas('packages.examination', function ($q) use ($request) {
                $q->where('gate_pass_marked_at', '>=', $request->input('gate_pass_date_from'));
            });
        }

        if ($request->filled('gate_pass_date_to')) {
            $query->whereHas('packages.examination', function ($q) use ($request) {
                $q->where('gate_pass_marked_at', '<=', $request->input('gate_pass_date_to') . ' 23:59:59');
            });
        }

        // Cashier Invoice Date Range
        if ($request->filled('cashier_invoice_date_from')) {
            $query->whereHas('cashierPayments', function ($q) use ($request) {
                $q->where('created_at', '>=', $request->input('cashier_invoice_date_from'));
            });
        }

        if ($request->filled('cashier_invoice_date_to')) {
            $query->whereHas('cashierPayments', function ($q) use ($request) {
                $q->where('created_at', '<=', $request->input('cashier_invoice_date_to') . ' 23:59:59');
            });
        }

        // Document Verified Date Range
        if ($request->filled('document_verified_date_from')) {
            $query->whereHas('verification', function ($q) use ($request) {
                $q->where('verified_at', '>=', $request->input('document_verified_date_from'));
            });
        }

        if ($request->filled('document_verified_date_to')) {
            $query->whereHas('verification', function ($q) use ($request) {
                $q->where('verified_at', '<=', $request->input('document_verified_date_to') . ' 23:59:59');
            });
        }

        // Shipment/Container filter
        if ($request->filled('container_reference')) {
            $query->whereHas('container', function ($q) use ($request) {
                $q->where('reference', 'like', '%' . $request->input('container_reference') . '%');
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
                $q->where('reference', 'like', "%{$search}%")
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
        $clonedQuery = clone $query;
        
        $totalHBLs = $clonedQuery->count();
        $totalAmount = (clone $query)->sum('grand_total');
        $totalPaid = (clone $query)->sum('paid_amount');
        $totalPackages = (clone $query)->withCount('packages')->get()->sum('packages_count');

        return [
            'total_hbls' => $totalHBLs,
            'total_amount' => number_format($totalAmount, 2),
            'total_paid' => number_format($totalPaid, 2),
            'total_packages' => $totalPackages,
        ];
    }

    /**
     * Transform HBL record for response
     */
    private function transformRecord(HBL $hbl): array
    {
        // Get loaded date (first package loaded)
        $loadedDate = $hbl->packages()
            ->join('container_hbl_package', 'hbl_packages.id', '=', 'container_hbl_package.hbl_package_id')
            ->whereNotNull('container_hbl_package.loaded_at')
            ->orderBy('container_hbl_package.loaded_at', 'asc')
            ->value('container_hbl_package.loaded_at');

        // Get unloaded date (last package unloaded)
        $unloadedDate = $hbl->packages()
            ->join('container_hbl_package', 'hbl_packages.id', '=', 'container_hbl_package.hbl_package_id')
            ->whereNotNull('container_hbl_package.unloaded_at')
            ->orderBy('container_hbl_package.unloaded_at', 'desc')
            ->value('container_hbl_package.unloaded_at');

        return [
            'id' => $hbl->id,
            'reference' => $hbl->reference,
            'hbl_name' => $hbl->hbl_name,
            'contact_number' => $hbl->contact_number,
            'email' => $hbl->email,
            'cargo_type' => $hbl->cargo_type,
            'hbl_type' => $hbl->hbl_type,
            'branch' => $hbl->branch ? [
                'id' => $hbl->branch->id,
                'name' => $hbl->branch->name,
            ] : null,
            'container_reference' => $hbl->container?->reference,
            'loaded_date' => $loadedDate ? date('Y-m-d H:i:s', strtotime($loadedDate)) : null,
            'unloaded_date' => $unloadedDate ? date('Y-m-d H:i:s', strtotime($unloadedDate)) : null,
            'appointment_date' => $hbl->callFlags()->latest()->first()?->appointment_date,
            'token_issued_date' => $hbl->token?->created_at?->format('Y-m-d H:i:s'),
            'token_number' => $hbl->token?->token,
            'document_verified_date' => $hbl->verification?->verified_at?->format('Y-m-d H:i:s'),
            'cashier_invoice_date' => $hbl->cashierPayments()->latest()->first()?->created_at?->format('Y-m-d H:i:s'),
            'gate_pass_date' => $hbl->packages()->whereHas('examination', function($q) {
                $q->whereNotNull('gate_pass_marked_at');
            })->with('examination')->first()?->examination?->gate_pass_marked_at?->format('Y-m-d H:i:s'),
            'total_packages' => $hbl->packages->count(),
            'grand_total' => number_format($hbl->grand_total, 2),
            'paid_amount' => number_format($hbl->paid_amount, 2),
            'balance' => number_format($hbl->grand_total - $hbl->paid_amount, 2),
            'created_at' => $hbl->created_at?->format('Y-m-d H:i:s'),
            'created_by' => $hbl->createdBy ? [
                'id' => $hbl->createdBy->id,
                'name' => $hbl->createdBy->name,
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
        $filename = 'hbl-report-' . date('Y-m-d-His');

        return Excel::download(
            new HBLReportExport($request),
            "{$filename}.{$format}"
        );
    }
}
