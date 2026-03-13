<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AgentWiseIncomeAnalysisExport;

class AgentWiseIncomeAnalysisController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('reports.agent-wise-income');

        return Inertia::render('Reports/AgentWiseIncomeAnalysis');
    }

    public function getData(Request $request)
    {
        $this->authorize('reports.agent-wise-income');

        try {
            // Build the query for agent wise income analysis
            $query = DB::table('branches')
                ->leftJoin('hbl', function($join) {
                    $join->on('branches.id', '=', 'hbl.branch_id')
                         ->whereNull('hbl.deleted_at');
                })
                ->leftJoin('hbl_packages', function($join) {
                    $join->on('hbl.id', '=', 'hbl_packages.hbl_id')
                         ->whereNull('hbl_packages.deleted_at');
                })
                ->leftJoin('cashier_hbl_payments', 'hbl.id', '=', 'cashier_hbl_payments.hbl_id')
                ->select([
                    'branches.name as agent_name',
                    DB::raw('COUNT(DISTINCT CASE WHEN hbl.consignee_id IS NOT NULL THEN hbl.consignee_id END) as no_of_cons'),
                    DB::raw('COUNT(DISTINCT CASE WHEN hbl_packages.id IS NOT NULL THEN hbl_packages.id END) as no_of_pkgs'),
                    DB::raw('COALESCE(SUM(CASE WHEN hbl_packages.volume IS NOT NULL THEN hbl_packages.volume ELSE 0 END), 0) as cbm'),
                    DB::raw('COALESCE(SUM(CASE WHEN cashier_hbl_payments.destination_slpa_charge IS NOT NULL THEN cashier_hbl_payments.destination_slpa_charge ELSE 0 END), 0) as slpa'),
                    DB::raw('COALESCE(SUM(CASE WHEN cashier_hbl_payments.destination_handling_charge IS NOT NULL THEN cashier_hbl_payments.destination_handling_charge ELSE 0 END), 0) as handling'),
                    DB::raw('COALESCE(SUM(CASE WHEN cashier_hbl_payments.destination_bond_charge IS NOT NULL THEN cashier_hbl_payments.destination_bond_charge ELSE 0 END), 0) as bond'),
                    DB::raw('COALESCE(SUM(CASE WHEN cashier_hbl_payments.destination_demurrage_charge IS NOT NULL THEN cashier_hbl_payments.destination_demurrage_charge ELSE 0 END), 0) as demurr'),
                    DB::raw('COALESCE(SUM(CASE WHEN cashier_hbl_payments.discount IS NOT NULL THEN cashier_hbl_payments.discount ELSE 0 END), 0) as discount'),
                    DB::raw('COALESCE(SUM(CASE WHEN cashier_hbl_payments.destination_1_tax IS NOT NULL THEN cashier_hbl_payments.destination_1_tax ELSE 0 END), 0) as vat'),
                    DB::raw('COALESCE(SUM(CASE WHEN cashier_hbl_payments.destination_2_tax IS NOT NULL THEN cashier_hbl_payments.destination_2_tax ELSE 0 END), 0) as nbt_paid'),
                    DB::raw('COALESCE(SUM(CASE WHEN cashier_hbl_payments.destination_1_total_with_tax IS NOT NULL THEN cashier_hbl_payments.destination_1_total_with_tax ELSE 0 END) + SUM(CASE WHEN cashier_hbl_payments.destination_2_total_with_tax IS NOT NULL THEN cashier_hbl_payments.destination_2_total_with_tax ELSE 0 END) + SUM(CASE WHEN cashier_hbl_payments.departure_grand_total IS NOT NULL THEN cashier_hbl_payments.departure_grand_total ELSE 0 END), 0) as total'),
                ])
                ->whereNull('branches.deleted_at')
                ->groupBy('branches.id', 'branches.name')
                ->orderBy('branches.name', 'asc');

            // Apply date range filters on HBL created_at
            if ($request->filled('date_from')) {
                $query->where(function($q) use ($request) {
                    $q->whereDate('hbl.created_at', '>=', $request->date_from)
                      ->orWhereNull('hbl.created_at');
                });
            }

            if ($request->filled('date_to')) {
                $query->where(function($q) use ($request) {
                    $q->whereDate('hbl.created_at', '<=', $request->date_to)
                      ->orWhereNull('hbl.created_at');
                });
            }

            // Apply search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where('branches.name', 'like', "%{$search}%");
            }

            // Get all records
            $agents = $query->get();

            // Transform data
            $records = $agents->map(function ($agent) {
                return [
                    'agent_name' => $agent->agent_name,
                    'no_of_cons' => (int) $agent->no_of_cons,
                    'no_of_pkgs' => (int) $agent->no_of_pkgs,
                    'cbm' => number_format((float) $agent->cbm, 2, '.', ''),
                    'slpa' => number_format((float) $agent->slpa, 2, '.', ''),
                    'handling' => number_format((float) $agent->handling, 2, '.', ''),
                    'bond' => number_format((float) $agent->bond, 2, '.', ''),
                    'demurr' => number_format((float) $agent->demurr, 2, '.', ''),
                    'discount' => number_format((float) $agent->discount, 2, '.', ''),
                    'vat' => number_format((float) $agent->vat, 2, '.', ''),
//                    'nbt_paid' => number_format((float) $agent->nbt_paid, 2, '.', ''),
                    'nbt_paid' => 0,
                    'total' => number_format((float) $agent->total, 2, '.', ''),
                ];
            });

            // Calculate statistics
            $stats = [
                'total_records' => $records->count(),
                'total_cons' => $records->sum(fn($r) => (int) $r['no_of_cons']),
                'total_pkgs' => $records->sum(fn($r) => (int) $r['no_of_pkgs']),
                'total_cbm' => number_format($records->sum(fn($r) => (float) $r['cbm']), 2, '.', ''),
                'grand_total' => number_format($records->sum(fn($r) => (float) $r['total']), 2, '.', ''),
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
            \Log::error('Agent Wise Income Analysis Error: ' . $e->getMessage(), [
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
        $this->authorize('reports.agent-wise-income');

        $format = $request->get('format', 'xlsx');
        $filename = 'agent-wise-income-analysis-' . now()->format('Y-m-d-His');

        $export = new AgentWiseIncomeAnalysisExport($request->all());

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
