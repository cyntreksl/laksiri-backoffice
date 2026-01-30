<?php

namespace App\Http\Controllers\CallCenter;

use App\Http\Controllers\Controller;
use App\Models\CashierHBLPayment;
use App\Models\HBL;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\CashierDailyCollectionExport;

class CashierReportController extends Controller
{
    public function debug()
    {
        $user = auth()->user();
        $dateFrom = now()->subDays(30)->format('Y-m-d');
        $dateTo = now()->format('Y-m-d');

        $userBranches = $user->branches->pluck('id', 'name')->toArray();
        $hblIds = HBL::withoutGlobalScopes()->whereIn('branch_id', array_values($userBranches))->pluck('id')->toArray();

        $query = CashierHBLPayment::query()
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        $allCount = $query->count();

        $filteredQuery = CashierHBLPayment::query()
            ->whereIn('hbl_id', $hblIds)
            ->whereBetween('created_at', [$dateFrom . ' 00:00:00', $dateTo . ' 23:59:59']);

        $filteredCount = $filteredQuery->count();

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'roles' => $user->roles->pluck('name'),
                'is_super_admin' => $user->hasRole('super admin'),
            ],
            'branches' => $userBranches,
            'date_range' => [
                'from' => $dateFrom,
                'to' => $dateTo,
            ],
            'hbl_ids_count' => count($hblIds),
            'hbl_ids_sample' => array_slice($hblIds, 0, 5),
            'all_payments_count' => $allCount,
            'filtered_payments_count' => $filteredCount,
            'sample_payment' => CashierHBLPayment::with(['token', 'hbl'])->first(),
        ]);
    }

    public function index()
    {
        return Inertia::render('CallCenter/Cashier/DailyCollectionReport', [
            'breadcrumbs' => [
                ['title' => 'Home', 'url' => route('dashboard')],
                ['title' => 'Cashier', 'url' => route('call-center.cashier.queue.list')],
                ['title' => 'Daily Collection Report', 'current' => true],
            ],
        ]);
    }

    public function getData(Request $request)
    {
        $query = CashierHBLPayment::query()
            ->with([
                'verifiedBy:id,name',
                'token:id,reference,customer_id',
                'token.customer:id,name,contact'
            ]);

        // Apply branch filter based on user permissions
        $user = auth()->user();

        // Filter by logged-in user - only show their own collections
        $query->where('verified_by', $user->id);

        // Super admins and call center users can see all branches
        if (!$user->hasRole(['super admin', 'call center'])) {
            $branchIds = $user->branches->pluck('id')->toArray();
            if (!empty($branchIds)) {
                // Get HBL IDs that belong to user's branches
                $hblIds = HBL::withoutGlobalScopes()
                    ->whereIn('branch_id', $branchIds)
                    ->pluck('id');
                $query->whereIn('hbl_id', $hblIds);
            } else {
                // User has no branches, return empty result
                $query->whereRaw('1 = 0');
            }
        }

        // Date range filter (default to last 30 days)
        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        if ($dateFrom && $dateTo) {
            $query->whereBetween('created_at', [
                $dateFrom . ' 00:00:00',
                $dateTo . ' 23:59:59'
            ]);
        }

        // HBL reference filter
        if ($request->filled('hbl_reference')) {
            $query->whereHas('token', function ($q) use ($request) {
                $q->where('reference', 'like', '%' . $request->input('hbl_reference') . '%');
            });
        }

        // Customer name filter
        if ($request->filled('customer_name')) {
            $query->whereHas('token.customer', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('customer_name') . '%');
            });
        }

        // Sorting
        $sortField = $request->input('sort_field', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $query->orderBy($sortField, $sortOrder);

        // Pagination
        $perPage = $request->input('per_page', 15);
        $payments = $query->paginate($perPage);

        // Calculate summary - use a fresh query without pagination
        $summaryQuery = CashierHBLPayment::query();

        // Filter by logged-in user
        $summaryQuery->where('verified_by', $user->id);

        // Apply same filters to summary
        if (!$user->hasRole(['super admin', 'call center'])) {
            $branchIds = $user->branches->pluck('id')->toArray();
            if (!empty($branchIds)) {
                $hblIds = HBL::withoutGlobalScopes()
                    ->whereIn('branch_id', $branchIds)
                    ->pluck('id');
                $summaryQuery->whereIn('hbl_id', $hblIds);
            } else {
                $summaryQuery->whereRaw('1 = 0');
            }
        }

        if ($dateFrom && $dateTo) {
            $summaryQuery->whereBetween('created_at', [
                $dateFrom . ' 00:00:00',
                $dateTo . ' 23:59:59'
            ]);
        }

        if ($request->filled('hbl_reference')) {
            $summaryQuery->whereHas('token', function ($q) use ($request) {
                $q->where('reference', 'like', '%' . $request->input('hbl_reference') . '%');
            });
        }

        if ($request->filled('customer_name')) {
            $summaryQuery->whereHas('token.customer', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('customer_name') . '%');
            });
        }

        $summary = [
            'total_transactions' => $summaryQuery->count(),
            'total_collected' => $summaryQuery->sum('paid_amount'),
            'payment_methods' => [
                'cash' => $summaryQuery->sum('paid_amount'), // Placeholder
                'card' => 0, // Placeholder
                'transfer' => 0, // Placeholder
            ]
        ];

        return response()->json([
            'data' => $payments->items(),
            'meta' => [
                'total' => $payments->total(),
                'current_page' => $payments->currentPage(),
                'per_page' => $payments->perPage(),
                'last_page' => $payments->lastPage(),
            ],
            'summary' => $summary
        ]);
    }

    public function exportPdf(Request $request)
    {
        $data = $this->getExportData($request);

        $pdf = Pdf::loadView('reports.cashier-daily-collection', $data);

        $filename = 'cashier-collection-' . now()->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }

    private function getExportData(Request $request)
    {
        $query = CashierHBLPayment::query()
            ->with([
                'verifiedBy:id,name',
                'token:id,reference,customer_id',
                'token.customer:id,name,contact'
            ]);

        // Apply same filters as getData
        $user = auth()->user();
        
        // Filter by logged-in user
        $query->where('verified_by', $user->id);
        
        if (!$user->hasRole(['super admin', 'call center'])) {
            $branchIds = $user->branches->pluck('id')->toArray();
            if (!empty($branchIds)) {
                $hblIds = HBL::withoutGlobalScopes()
                    ->whereIn('branch_id', $branchIds)
                    ->pluck('id');
                $query->whereIn('hbl_id', $hblIds);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        $dateFrom = $request->input('date_from', now()->subDays(30)->format('Y-m-d'));
        $dateTo = $request->input('date_to', now()->format('Y-m-d'));

        if ($dateFrom && $dateTo) {
            $query->whereBetween('created_at', [
                $dateFrom . ' 00:00:00',
                $dateTo . ' 23:59:59'
            ]);
        }

        if ($request->filled('hbl_reference')) {
            $query->whereHas('token', function ($q) use ($request) {
                $q->where('reference', 'like', '%' . $request->input('hbl_reference') . '%');
            });
        }

        if ($request->filled('customer_name')) {
            $query->whereHas('token.customer', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->input('customer_name') . '%');
            });
        }

        $payments = $query->orderBy('created_at', 'desc')->get();

        $summary = [
            'total_transactions' => $payments->count(),
            'total_collected' => $payments->sum('paid_amount'),
            'date_from' => $dateFrom,
            'date_to' => $dateTo,
        ];

        return [
            'payments' => $payments,
            'summary' => $summary,
            'filters' => $request->all()
        ];
    }

    public function exportExcel(Request $request)
    {
        $data = $this->getExportData($request);

        $filename = 'cashier-collection-' . now()->format('Y-m-d') . '.xlsx';

        return Excel::download(new CashierDailyCollectionExport($data), $filename);
    }
}
