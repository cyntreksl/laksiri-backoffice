<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\UnclearedRTFConsigneeDetailsExport;

class UnclearedRTFConsigneeDetailsController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('reports.uncleared-rtf-consignee');

        return Inertia::render('Reports/UnclearedRTFConsigneeDetails');
    }

    public function getData(Request $request)
    {
        $this->authorize('reports.uncleared-rtf-consignee');

        try {
            // Build the query for uncleared RTF consignee details
            $query = DB::table('hbl')
                ->join('users as consignees', 'hbl.consignee_id', '=', 'consignees.id')
                ->join('branches', 'hbl.branch_id', '=', 'branches.id')
                ->leftJoin('hbl_packages', function($join) {
                    $join->on('hbl.id', '=', 'hbl_packages.hbl_id')
                         ->whereNull('hbl_packages.deleted_at');
                })
                ->leftJoin('detain_records', function($join) {
                    $join->on('hbl_packages.id', '=', 'detain_records.rtfable_id')
                         ->where('detain_records.rtfable_type', '=', 'App\\Models\\HBLPackage')
                         ->where('detain_records.is_rtf', '=', true)
                         ->whereNull('detain_records.lifted_at'); // Only active RTF records
                })
                ->select([
                    'hbl.hbl_number',
                    'consignees.name as consignee_name',
                    'branches.name as agent_name',
                    'hbl.created_at as date',
                    DB::raw('COUNT(DISTINCT CASE WHEN hbl_packages.id IS NOT NULL THEN hbl_packages.id END) as no_of_pkgs'),
                    DB::raw('COALESCE(SUM(CASE WHEN hbl_packages.volume IS NOT NULL THEN hbl_packages.volume ELSE 0 END), 0) as cbm'),
                ])
                ->whereNull('hbl.deleted_at')
                ->whereNotNull('detain_records.id') // Must have RTF detain record
                ->where(function($query) {
                    $query->where('hbl.status', '!=', 'completed')
                          ->orWhereNull('hbl.status');
                }) // Uncleared means not completed
                ->groupBy(
                    'hbl.id',
                    'hbl.hbl_number',
                    'consignees.name',
                    'branches.name',
                    'hbl.created_at'
                )
                ->orderBy('hbl.created_at', 'desc');

            // Apply date range filters
            if ($request->filled('date_from')) {
                $query->whereDate('hbl.created_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('hbl.created_at', '<=', $request->date_to);
            }

            // Apply search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('hbl.hbl_number', 'like', "%{$search}%")
                        ->orWhere('consignees.name', 'like', "%{$search}%")
                        ->orWhere('branches.name', 'like', "%{$search}%");
                });
            }

            // Get all records
            $records = $query->get();

            // Transform data
            $transformedRecords = $records->map(function ($record, $index) {
                return [
                    'serial_no' => $index + 1,
                    'hbl_number' => $record->hbl_number,
                    'consignee_name' => $record->consignee_name,
                    'no_of_pkgs' => (int) $record->no_of_pkgs,
                    'cbm' => number_format((float) $record->cbm, 3, '.', ''),
                    'date' => $record->date ? date('Y-m-d', strtotime($record->date)) : '',
                    'agent_name' => $record->agent_name,
                ];
            });

            // Calculate statistics
            $stats = [
                'total_records' => $transformedRecords->count(),
                'total_pkgs' => $transformedRecords->sum(fn($r) => (int) $r['no_of_pkgs']),
                'total_cbm' => number_format($transformedRecords->sum(fn($r) => (float) $r['cbm']), 3, '.', ''),
            ];

            // Pagination
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 25);
            $total = $transformedRecords->count();
            $paginatedRecords = $transformedRecords->slice(($page - 1) * $perPage, $perPage)->values();

            return response()->json([
                'success' => true,
                'data' => $paginatedRecords,
                'total' => $total,
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            \Log::error('Uncleared RTF Consignee Details Error: ' . $e->getMessage(), [
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
        $this->authorize('reports.uncleared-rtf-consignee');

        $format = $request->get('format', 'xlsx');
        $filename = 'uncleared-rtf-consignee-details-' . now()->format('Y-m-d-His');

        $export = new UnclearedRTFConsigneeDetailsExport($request->all());

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