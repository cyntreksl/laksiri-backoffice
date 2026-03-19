<?php

namespace App\Http\Controllers;

use App\Exports\ConsigneeVolumeAnalysisExport;
use App\Models\Branch;
use App\Models\Examination;
use App\Models\HBL;
use App\Models\Scopes\BranchScope;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Maatwebsite\Excel\Facades\Excel;

class ConsigneeVolumeAnalysisController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display the Consignee & Volume Analysis report page
     */
    public function index(): Response
    {
        $this->authorize('reports.consignee-volume-analysis');

        return Inertia::render('Reports/ConsigneeVolumeAnalysis', [
            'branches' => $this->getBranches(),
        ]);
    }

    /**
     * Get branches for filter
     */
    private function getBranches(): array
    {
        return Branch::select('id', 'name', 'branch_code')
            ->orderBy('name')
            ->get()
            ->map(fn($branch) => [
                'value' => $branch->id,
                'label' => $branch->name,
                'code' => $branch->branch_code ?? ''
            ])
            ->toArray();
    }

    /**
     * Get Consignee & Volume Analysis report data with filters
     */
    public function getData(Request $request): JsonResponse
    {
        $this->authorize('reports.consignee-volume-analysis');

        // Require analysis type selection
        if (!$request->filled('analysis_type')) {
            return response()->json([
                'success' => true,
                'data' => [],
                'total' => 0,
                'stats' => [
                    'total_agents' => 0,
                    'total_consignees' => 0,
                    'total_packages_manifest' => 0,
                    'total_packages_actual' => 0,
                    'total_cbm' => 0,
                ],
                'per_page' => 25,
                'current_page' => 1,
                'last_page' => 1,
            ]);
        }

        $analysisType = $request->input('analysis_type');
        $isOutgoing = $analysisType === 'outgoing';

        // Build query based on analysis type
        $query = $this->buildAnalysisQuery($request, $isOutgoing);

        // Get data grouped by branch
        $results = $query->get();

        // Transform data for response
        $data = $results->map(function ($result) {
            return [
                'id' => $result->branch_id,
                'agent_name' => $result->agent_name,
                'no_of_consignees' => (int) $result->no_of_consignees,
                'no_of_pkgs_manifest' => (int) $result->no_of_pkgs_manifest,
                'no_of_pkgs_actual' => (int) $result->no_of_pkgs_actual,
                'cbm' => number_format((float) $result->cbm, 2),
            ];
        });

        // Calculate statistics
        $stats = [
            'total_agents' => $results->count(),
            'total_consignees' => $results->sum('no_of_consignees'),
            'total_packages_manifest' => $results->sum('no_of_pkgs_manifest'),
            'total_packages_actual' => $results->sum('no_of_pkgs_actual'),
            'total_cbm' => number_format($results->sum('cbm'), 2),
        ];

        return response()->json([
            'success' => true,
            'data' => $data,
            'total' => $results->count(),
            'stats' => $stats,
            'per_page' => 100, // Show all results
            'current_page' => 1,
            'last_page' => 1,
        ]);
    }

    /**
     * Build analysis query based on type
     */
    private function buildAnalysisQuery(Request $request, bool $isOutgoing): \Illuminate\Database\Query\Builder
    {
        $gatePassValue = $isOutgoing ? 1 : 0;

        $query = DB::table('examinations as e')
            ->join('hbl as h', 'e.hbl_id', '=', 'h.id')
            ->join('branches as b', 'h.branch_id', '=', 'b.id')
            ->join('hbl_packages as hp', 'h.id', '=', 'hp.hbl_id')
            ->where('e.is_issued_gate_pass', $gatePassValue)
            ->whereNull('h.deleted_at')
            ->whereNull('hp.deleted_at')
            ->select([
                'h.branch_id',
                'b.name as agent_name',
                DB::raw('COUNT(DISTINCT h.id) as no_of_consignees'),
                DB::raw('SUM(hp.quantity) as no_of_pkgs_manifest'),
                DB::raw('SUM(hp.quantity) as no_of_pkgs_actual'), // Assuming manifest = actual for now
                DB::raw('SUM(hp.volume) as cbm')
            ])
            ->groupBy('h.branch_id', 'b.name');

        // Apply filters
        $this->applyFilters($query, $request);

        return $query->orderBy('b.name');
    }

    /**
     * Apply filters to query
     */
    private function applyFilters($query, Request $request): void
    {
        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('e.created_at', '>=', $request->input('date_from'));
        }

        if ($request->filled('date_to')) {
            $query->whereDate('e.created_at', '<=', $request->input('date_to'));
        }

        // Branch filter
        if ($request->filled('branch_id')) {
            $query->where('h.branch_id', $request->input('branch_id'));
        }

        // General search
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('b.name', 'like', "%{$search}%");
        }
    }

    /**
     * Export Consignee & Volume Analysis report
     */
    public function export(Request $request)
    {
        $this->authorize('reports.consignee-volume-analysis');

        // Require analysis type for export
        if (!$request->filled('analysis_type')) {
            return response()->json([
                'success' => false,
                'message' => 'Please select an analysis type before exporting'
            ], 400);
        }

        try {
            $format = $request->input('format', 'xlsx');
            $analysisType = $request->input('analysis_type');
            $filename = 'consignee-volume-analysis-' . $analysisType . '-' . date('Y-m-d-His');

            // Debug: Log the request parameters
            \Log::info('Consignee Volume Analysis Export Request', [
                'format' => $format,
                'params' => $request->all()
            ]);

            // Create export instance
            $export = new ConsigneeVolumeAnalysisExport($request);

            // Handle different formats
            switch ($format) {
                case 'pdf':
                    return Excel::download($export, $filename . '.pdf', \Maatwebsite\Excel\Excel::DOMPDF);
                case 'csv':
                    return Excel::download($export, $filename . '.csv', \Maatwebsite\Excel\Excel::CSV);
                default:
                    return Excel::download($export, $filename . '.xlsx');
            }

        } catch (\Exception $e) {
            \Log::error('Consignee Volume Analysis Export Error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Export failed: ' . $e->getMessage()
            ], 500);
        }
    }
}