<?php

namespace App\Http\Controllers;

use App\Exports\StampDutyReportExport;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class StampDutyReportController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('reports.stamp-duty');

        return Inertia::render('Reports/StampDutyReport');
    }

    public function getData(Request $request)
    {
        $this->authorize('reports.stamp-duty');

        try {
            $query = DB::table('cashier_hbl_payments')
                ->join('hbl', 'cashier_hbl_payments.hbl_id', '=', 'hbl.id')
                ->select([
                    'cashier_hbl_payments.created_at',
                    'cashier_hbl_payments.invoice_number',
                    'cashier_hbl_payments.destination_stamp_charge',
                    'hbl.cargo_type',
                ])
                ->whereNotNull('cashier_hbl_payments.invoice_number')
                ->whereNotNull('cashier_hbl_payments.destination_stamp_charge')
                ->where('cashier_hbl_payments.destination_stamp_charge', '>', 0)
                ->whereNull('hbl.deleted_at')
                ->orderBy('cashier_hbl_payments.created_at', 'asc');

            // Apply date range filters
            if ($request->filled('date_from')) {
                $query->whereDate('cashier_hbl_payments.created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('cashier_hbl_payments.created_at', '<=', $request->date_to);
            }

            // Apply cargo type filter
            if ($request->filled('cargo_type')) {
                $query->where('hbl.cargo_type', $request->cargo_type);
            }

            // Apply search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where('cashier_hbl_payments.invoice_number', 'like', "%{$search}%");
            }

            // Get all records
            $payments = $query->get();

            // Transform data
            $records = $payments->map(function ($payment, $index) {
                return [
                    'date' => date('d/m/Y', strtotime($payment->created_at)),
                    'invoice_number' => $payment->invoice_number,
                    'amount' => number_format($payment->destination_stamp_charge, 2, '.', ''),
                    'cargo_type' => $payment->cargo_type,
                ];
            });

            // Calculate statistics
            $stats = [
                'total_records' => $records->count(),
                'grand_total' => number_format($records->sum(fn($r) => (float) $r['amount']), 2, '.', ''),
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
            \Log::error('Stamp Duty Report Error: ' . $e->getMessage(), [
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
        $this->authorize('reports.stamp-duty');

        $format = $request->get('format', 'xlsx');
        $filename = 'stamp-duty-report-' . now()->format('Y-m-d-His');

        $export = new StampDutyReportExport($request->all());

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
