<?php

namespace App\Http\Controllers;

use App\Exports\DailyCollectionReportExport;
use App\Models\CashierHblPayment;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class DailyCollectionReportController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('reports.daily-collection');

        return Inertia::render('Reports/DailyCollectionReport');
    }

    public function getData(Request $request)
    {
        $this->authorize('reports.daily-collection');

        try {
            $query = CashierHblPayment::query()
                ->whereNotNull('invoice_number')
                ->orderBy('created_at', 'asc');

            // Apply date filter
            if ($request->filled('date')) {
                $query->whereDate('created_at', $request->date);
            }

            // Apply date range filters
            if ($request->filled('date_from')) {
                $query->whereDate('created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('created_at', '<=', $request->date_to);
            }

            // Apply search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%");
                });
            }

            // Get all records
            $payments = $query->get();

            // Transform data with serial numbers and tax calculations
            $records = $payments->map(function ($payment, $index) {
                // VAT and NBT are stored directly in the payment record
                $vatAmount = (float) ($payment->destination_1_tax ?? 0);
                $nbtAmount = (float) (0);

                // Calculate total amount from all charges
                $departureTotal = (float) ($payment->departure_grand_total ?? 0);
                $destinationTotal = (float) ($payment->destination_1_total_with_tax ?? 0) +
                                   (float) ($payment->destination_2_total_with_tax ?? 0);
                $totalAmount = $departureTotal + $destinationTotal;

                // If paid_amount is set and greater than 0, use it as the total
                if ($payment->paid_amount > 0) {
                    $totalAmount = (float) $payment->paid_amount;
                }

                return [
                    'serial_no' => $index + 1,
                    'invoice_number' => $payment->invoice_number,
                    'vat' => number_format($vatAmount, 2, '.', ''),
                    'nbt' => number_format($nbtAmount, 2, '.', ''),
                    'total_amount' => number_format($totalAmount, 2, '.', ''),
                    'created_at' => $payment->created_at->format('Y-m-d H:i:s'),
                ];
            });

            // Calculate statistics
            $stats = [
                'total_records' => $records->count(),
                'total_vat' => number_format($records->sum(fn($r) => (float) $r['vat']), 2, '.', ''),
                'total_nbt' => number_format($records->sum(fn($r) => (float) $r['nbt']), 2, '.', ''),
                'grand_total' => number_format($records->sum(fn($r) => (float) $r['total_amount']), 2, '.', ''),
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
            \Log::error('Daily Collection Report Error: ' . $e->getMessage(), [
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
        $this->authorize('reports.daily-collection');

        $format = $request->get('format', 'xlsx');
        $date = $request->get('date');

        $filename = 'daily-collection-report-' . ($date ?? now()->format('Y-m-d')) . '-' . now()->format('His');

        $export = new DailyCollectionReportExport($request->all());

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
