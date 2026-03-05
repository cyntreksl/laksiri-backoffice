<?php

namespace App\Http\Controllers;

use App\Exports\FreightChargesReportExport;
use App\Models\HBL;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class FreightChargesReportController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('reports.freight-charges');

        return Inertia::render('Reports/FreightChargesReport');
    }

    public function getData(Request $request)
    {
        $this->authorize('reports.freight-charges');

        try {
            $query = HBL::query()
                ->with([
                    'departureCharge',
                    'branch',
                    'consignee',
                ])
                ->whereHas('departureCharge')
                ->orderBy('created_at', 'asc');

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
                    $q->where('hbl_number', 'like', "%{$search}%")
                        ->orWhereHas('consignee', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('branch', function ($q) use ($search) {
                            $q->where('name', 'like', "%{$search}%");
                        });
                });
            }

            // Get all records
            $hbls = $query->get();

            // Get invoice numbers for all HBLs
            $hblIds = $hbls->pluck('id')->toArray();
            $invoiceNumbers = \DB::table('cashier_hbl_payments')
                ->whereIn('hbl_id', $hblIds)
                ->whereNotNull('invoice_number')
                ->pluck('invoice_number', 'hbl_id')
                ->toArray();

            // Transform data
            $records = $hbls->map(function ($hbl) use ($invoiceNumbers) {
                $departureCharge = $hbl->departureCharge;
                $freightCharge = $departureCharge ? (float) ($departureCharge->freight_charge ?? 0) : 0;

                return [
                    'date' => $hbl->created_at->format('d/m/Y'),
                    'hbl_no' => $hbl->hbl_number,
                    'agent_name' => $hbl->branch ? $hbl->branch->name : 'N/A',
                    'invoice_no' => $invoiceNumbers[$hbl->id] ?? '',
                    'consignee_name' => $hbl->consignee ? $hbl->consignee->name : 'N/A',
                    'amount' => number_format($freightCharge, 2, '.', ''),
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
            \Log::error('Freight Charges Report Error: ' . $e->getMessage(), [
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
        $this->authorize('reports.freight-charges');

        $format = $request->get('format', 'xlsx');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $filename = 'freight-charges-report-' . now()->format('Y-m-d-His');

        $export = new FreightChargesReportExport($request->all());

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
