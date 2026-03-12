<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AgentWiseConsigneeVolumeAnalysisExport;

class AgentWiseConsigneeVolumeAnalysisController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('reports.agent-wise-consignee-volume');

        return Inertia::render('Reports/AgentWiseConsigneeVolumeAnalysis');
    }

    public function getData(Request $request)
    {
        $this->authorize('reports.agent-wise-consignee-volume');

        try {
            // Build the query for agent wise consignee & volume analysis
            $query = DB::table('branches')
                ->leftJoin('hbl', function($join) {
                    $join->on('branches.id', '=', 'hbl.branch_id')
                         ->whereNull('hbl.deleted_at');
                })
                ->leftJoin('hbl_packages', function($join) {
                    $join->on('hbl.id', '=', 'hbl_packages.hbl_id')
                         ->whereNull('hbl_packages.deleted_at');
                })
                ->select([
                    'branches.name as agent_name',
                    DB::raw('COUNT(DISTINCT CASE WHEN hbl.consignee_id IS NOT NULL THEN hbl.consignee_id END) as no_of_consignees'),
                    DB::raw('COUNT(DISTINCT CASE WHEN hbl_packages.id IS NOT NULL THEN hbl_packages.id END) as no_of_pkgs_manifest'),
                    DB::raw('COUNT(DISTINCT CASE WHEN hbl_packages.id IS NOT NULL THEN hbl_packages.id END) as no_of_pkgs_actual'),
                    DB::raw('COALESCE(SUM(CASE WHEN hbl_packages.volume IS NOT NULL THEN hbl_packages.volume ELSE 0 END), 0) as cbm'),
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
                    'no_of_consignees' => (int) $agent->no_of_consignees,
                    'no_of_pkgs_manifest' => (int) $agent->no_of_pkgs_manifest,
                    'no_of_pkgs_actual' => (int) $agent->no_of_pkgs_actual,
                    'cbm' => number_format((float) $agent->cbm, 2, '.', ''),
                ];
            });

            // Calculate statistics
            $stats = [
                'total_records' => $records->count(),
                'total_consignees' => $records->sum(fn($r) => (int) $r['no_of_consignees']),
                'total_pkgs_manifest' => $records->sum(fn($r) => (int) $r['no_of_pkgs_manifest']),
                'total_pkgs_actual' => $records->sum(fn($r) => (int) $r['no_of_pkgs_actual']),
                'total_cbm' => number_format($records->sum(fn($r) => (float) $r['cbm']), 2, '.', ''),
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
            \Log::error('Agent Wise Consignee Volume Analysis Error: ' . $e->getMessage(), [
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
        $this->authorize('reports.agent-wise-consignee-volume');

        $format = $request->get('format', 'xlsx');
        $filename = 'agent-wise-consignee-volume-analysis-' . now()->format('Y-m-d-His');

        $export = new AgentWiseConsigneeVolumeAnalysisExport($request->all());

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
