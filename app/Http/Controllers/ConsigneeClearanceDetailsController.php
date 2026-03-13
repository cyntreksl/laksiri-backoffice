<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ConsigneeClearanceDetailsExport;

class ConsigneeClearanceDetailsController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('reports.consignee-clearance');

        return Inertia::render('Reports/ConsigneeClearanceDetails');
    }

    public function getData(Request $request)
    {
        $this->authorize('reports.consignee-clearance');

        try {
            // Build the query for consignee clearance details
            $query = DB::table('examinations')
                ->join('hbl', 'examinations.hbl_id', '=', 'hbl.id')
                ->join('users as consignees', 'hbl.consignee_id', '=', 'consignees.id')
                ->leftJoin('cashier_hbl_payments', 'hbl.id', '=', 'cashier_hbl_payments.hbl_id')
                ->select([
                    DB::raw('ROW_NUMBER() OVER (ORDER BY examinations.created_at DESC) as serial_no'),
                    'hbl.hbl_number',
                    'consignees.name as consignee_name',
                    'cashier_hbl_payments.invoice_number',
                    'cashier_hbl_payments.created_at as inv_date',
                    'examinations.created_at as det_date',
                ])
                ->whereNotNull('examinations.released_at') // Use released_at as clearance indicator
                ->whereNull('hbl.deleted_at')
                ->where('is_issued_gate_pass', TRUE)
                ->orderBy('examinations.created_at', 'desc');

            // Apply date range filters on examination created_at
            if ($request->filled('date_from')) {
                $query->whereDate('examinations.created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('examinations.created_at', '<=', $request->date_to);
            }

            // Apply search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('hbl.hbl_number', 'like', "%{$search}%")
                        ->orWhere('consignees.name', 'like', "%{$search}%")
                        ->orWhere('cashier_hbl_payments.invoice_number', 'like', "%{$search}%");
                });
            }

            // Get all records
            $clearances = $query->get();

            // Transform data with sequential serial numbers
            $records = $clearances->map(function ($clearance, $index) {
                return [
                    'serial_no' => $index + 1,
                    'hbl_number' => $clearance->hbl_number,
                    'consignee_name' => $clearance->consignee_name,
                    'invoice_number' => $clearance->invoice_number,
                    'inv_date' => $clearance->inv_date ? date('Y-m-d', strtotime($clearance->inv_date)) : '',
//                    'det_date' => $clearance->det_date ? date('Y-m-d', strtotime($clearance->det_date)) : '',
                    'det_date' => '',
                ];
            });

            // Calculate statistics
            $stats = [
                'total_records' => $records->count(),
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
            \Log::error('Consignee Clearance Details Error: ' . $e->getMessage(), [
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
        $this->authorize('reports.consignee-clearance');

        $format = $request->get('format', 'xlsx');
        $filename = 'consignee-clearance-details-' . now()->format('Y-m-d-His');

        $export = new ConsigneeClearanceDetailsExport($request->all());

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
