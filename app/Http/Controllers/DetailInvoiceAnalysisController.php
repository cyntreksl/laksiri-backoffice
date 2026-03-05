<?php

namespace App\Http\Controllers;

use App\Exports\DetailInvoiceAnalysisExport;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class DetailInvoiceAnalysisController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('reports.detail-invoice-analysis');

        return Inertia::render('Reports/DetailInvoiceAnalysisReport');
    }

    public function getData(Request $request)
    {
        $this->authorize('reports.detail-invoice-analysis');

        try {
            $query = DB::table('cashier_hbl_payments')
                ->join('hbl', 'cashier_hbl_payments.hbl_id', '=', 'hbl.id')
                ->join('hbl_packages', 'hbl.id', '=', 'hbl_packages.hbl_id')
                ->select([
                    'cashier_hbl_payments.invoice_number',
                    'hbl.hbl_number',
                    DB::raw('COUNT(hbl_packages.id) as no_of_pkgs'),
                    DB::raw('SUM(hbl_packages.volume) as cbm'),
                    'cashier_hbl_payments.destination_slpa_charge',
                    'cashier_hbl_payments.destination_handling_charge',
                    'cashier_hbl_payments.destination_bond_charge',
                    'cashier_hbl_payments.destination_demurrage_charge',
                    'cashier_hbl_payments.destination_1_tax as vat',
                    'cashier_hbl_payments.discount',
                    DB::raw('(COALESCE(cashier_hbl_payments.destination_1_total_with_tax, 0) + COALESCE(cashier_hbl_payments.destination_2_total_with_tax, 0) + COALESCE(cashier_hbl_payments.departure_grand_total, 0)) as total'),
                    'cashier_hbl_payments.created_at',
                ])
                ->whereNotNull('cashier_hbl_payments.invoice_number')
                ->whereNull('hbl.deleted_at')
                ->whereNull('hbl_packages.deleted_at')
                ->groupBy(
                    'cashier_hbl_payments.id',
                    'cashier_hbl_payments.invoice_number',
                    'hbl.hbl_number',
                    'cashier_hbl_payments.destination_slpa_charge',
                    'cashier_hbl_payments.destination_handling_charge',
                    'cashier_hbl_payments.destination_bond_charge',
                    'cashier_hbl_payments.destination_demurrage_charge',
                    'cashier_hbl_payments.destination_1_tax',
                    'cashier_hbl_payments.discount',
                    'cashier_hbl_payments.destination_1_total_with_tax',
                    'cashier_hbl_payments.destination_2_total_with_tax',
                    'cashier_hbl_payments.departure_grand_total',
                    'cashier_hbl_payments.created_at'
                )
                ->orderBy('cashier_hbl_payments.created_at', 'asc');

            // Apply date range filters
            if ($request->filled('date_from')) {
                $query->whereDate('cashier_hbl_payments.created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('cashier_hbl_payments.created_at', '<=', $request->date_to);
            }

            // Apply search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('cashier_hbl_payments.invoice_number', 'like', "%{$search}%")
                        ->orWhere('hbl.hbl_number', 'like', "%{$search}%");
                });
            }

            // Get all records
            $payments = $query->get();

            // Transform data
            $records = $payments->map(function ($payment) {
                return [
                    'invoice_number' => $payment->invoice_number,
                    'hbl_number' => $payment->hbl_number,
                    'no_of_pkgs' => (int) $payment->no_of_pkgs,
                    'cbm' => number_format($payment->cbm ?? 0, 3, '.', ''),
                    'slpa' => number_format($payment->destination_slpa_charge ?? 0, 2, '.', ''),
                    'handling' => number_format($payment->destination_handling_charge ?? 0, 2, '.', ''),
                    'bond' => number_format($payment->destination_bond_charge ?? 0, 2, '.', ''),
                    'demu' => number_format($payment->destination_demurrage_charge ?? 0, 2, '.', ''),
                    'vat' => number_format($payment->vat ?? 0, 2, '.', ''),
                    'discount' => number_format($payment->discount ?? 0, 2, '.', ''),
                    'total' => number_format($payment->total ?? 0, 2, '.', ''),
                ];
            });

            // Calculate statistics
            $stats = [
                'total_records' => $records->count(),
                'grand_total' => number_format($records->sum(fn($r) => (float) str_replace(',', '', $r['total'])), 2, '.', ''),
            ];

            // Pagination
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 25);
            $total = $records->count();
            $paginatedRecords = $records->slice(($page - 1) * $perPage, $perPage)->values();

            return response()->json([
                'success' => true,
                'data' => $paginatedRecords,
                'total' => $total,
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            \Log::error('Detail Invoice Analysis Report Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function export(Request $request)
    {
        $this->authorize('reports.detail-invoice-analysis');

        $format = $request->get('format', 'xlsx');
        $filename = 'detail-invoice-analysis-' . now()->format('Y-m-d-His');

        $export = new DetailInvoiceAnalysisExport($request->all());

        switch ($format) {
            case 'pdf':
                return Excel::download($export, $filename . '.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
            case 'csv':
                return Excel::download($export, $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
            default:
                return Excel::download($export, $filename . '.xlsx');
        }
    }
}
