<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ContainerWiseIncomeAnalysisExport;

class ContainerWiseIncomeAnalysisController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('reports.container-wise-income');

        return Inertia::render('Reports/ContainerWiseIncomeAnalysis');
    }

    public function getData(Request $request)
    {
        $this->authorize('reports.container-wise-income');

        try {
            // Build the query
            $query = DB::table('containers')
                ->join('branches', 'containers.branch_id', '=', 'branches.id')
                ->leftJoin('container_hbl_package', 'containers.id', '=', 'container_hbl_package.container_id')
                ->leftJoin('hbl_packages', 'container_hbl_package.hbl_package_id', '=', 'hbl_packages.id')
                ->leftJoin('hbl', 'hbl_packages.hbl_id', '=', 'hbl.id')
                ->leftJoin('cashier_hbl_payments', 'hbl.id', '=', 'cashier_hbl_payments.hbl_id')
                ->select([
                    'containers.container_number',
                    'containers.unloading_started_at as destuff_date',
                    'branches.name as agent_name',
                    DB::raw('COUNT(DISTINCT hbl.consignee_id) as no_of_cons'),
                    DB::raw('COUNT(DISTINCT hbl_packages.id) as no_of_pkgs'),
                    DB::raw('COALESCE(SUM(hbl_packages.volume), 0) as cbm'),
                    DB::raw('COALESCE(SUM(cashier_hbl_payments.destination_slpa_charge), 0) as slpa'),
                    DB::raw('COALESCE(SUM(cashier_hbl_payments.destination_handling_charge), 0) as handling'),
                    DB::raw('COALESCE(SUM(cashier_hbl_payments.destination_bond_charge), 0) as bond'),
                    DB::raw('COALESCE(SUM(cashier_hbl_payments.destination_demurrage_charge), 0) as demurr'),
                    DB::raw('COALESCE(SUM(cashier_hbl_payments.departure_freight_charge), 0) as frt_chg'),
                    DB::raw('COALESCE(SUM(cashier_hbl_payments.destination_do_charge), 0) as doc_chg'),
                    DB::raw('COALESCE(SUM(cashier_hbl_payments.destination_1_tax), 0) as vat'),
                    DB::raw('COALESCE(SUM(cashier_hbl_payments.destination_2_tax), 0) as nbt_paid'),
                    DB::raw('(COALESCE(SUM(cashier_hbl_payments.destination_1_total_with_tax), 0) + COALESCE(SUM(cashier_hbl_payments.destination_2_total_with_tax), 0) + COALESCE(SUM(cashier_hbl_payments.departure_grand_total), 0)) as total'),
                ])
                ->whereNotNull('containers.unloading_started_at')
                ->whereNull('containers.deleted_at')
                ->whereNull('hbl.deleted_at')
                ->whereNull('hbl_packages.deleted_at')
                ->groupBy(
                    'containers.id',
                    'containers.container_number',
                    'containers.unloading_started_at',
                    'branches.name'
                )
                ->orderBy('containers.unloading_started_at', 'desc');

            // Apply date range filters
            if ($request->filled('date_from')) {
                $query->whereDate('containers.unloading_started_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('containers.unloading_started_at', '<=', $request->date_to);
            }

            // Apply search filter
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('containers.container_number', 'like', "%{$search}%")
                        ->orWhere('branches.name', 'like', "%{$search}%");
                });
            }

            // Get all records
            $containers = $query->get();

            // Transform data
            $records = $containers->map(function ($container) {
                return [
                    'container_number' => $container->container_number,
                    'destuff_date' => $container->destuff_date ? date('Y-m-d', strtotime($container->destuff_date)) : '',
                    'agent_name' => $container->agent_name,
                    'no_of_cons' => (int) $container->no_of_cons,
                    'no_of_pkgs' => (int) $container->no_of_pkgs,
                    'cbm' => number_format((float) $container->cbm, 3, '.', ''),
                    'slpa' => number_format((float) $container->slpa, 2, '.', ''),
                    'handling' => number_format((float) $container->handling, 2, '.', ''),
                    'bond' => number_format((float) $container->bond, 2, '.', ''),
                    'demurr' => number_format((float) $container->demurr, 2, '.', ''),
                    'frt_chg' => number_format((float) $container->frt_chg, 2, '.', ''),
                    'doc_chg' => number_format((float) $container->doc_chg, 2, '.', ''),
                    'vat' => number_format((float) $container->vat, 2, '.', ''),
                    'nbt_paid' => number_format((float) $container->nbt_paid, 2, '.', ''),
                    'total' => number_format((float) $container->total, 2, '.', ''),
                ];
            });

            // Calculate statistics
            $stats = [
                'total_records' => $records->count(),
                'total_cons' => $records->sum(fn($r) => (int) $r['no_of_cons']),
                'total_pkgs' => $records->sum(fn($r) => (int) $r['no_of_pkgs']),
                'total_cbm' => number_format($records->sum(fn($r) => (float) $r['cbm']), 3, '.', ''),
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
            \Log::error('Container Wise Income Analysis Error: ' . $e->getMessage(), [
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
        $this->authorize('reports.container-wise-income');

        $format = $request->get('format', 'xlsx');
        $filename = 'container-wise-income-analysis-' . now()->format('Y-m-d-His');

        $export = new ContainerWiseIncomeAnalysisExport($request->all());

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
