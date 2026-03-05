<?php

namespace App\Http\Controllers;

use App\Enum\ContainerStatus;
use App\Exports\AgentWiseContainerArrivalSummaryExport;
use App\Models\Branch;
use App\Models\Container;
use App\Models\Scopes\BranchScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Maatwebsite\Excel\Facades\Excel;

class AgentWiseContainerArrivalSummaryController extends Controller
{
    public function index()
    {
        $branches = Branch::select('id', 'name')
            ->orderBy('name')
            ->get()
            ->map(fn($branch) => [
                'value' => $branch->id,
                'label' => $branch->name
            ])
            ->toArray();

        return Inertia::render('Reports/AgentWiseContainerArrivalSummary', [
            'branches' => $branches,
        ]);
    }

    public function getData(Request $request)
    {
        try {
            $query = Container::withoutGlobalScope(BranchScope::class)
                ->whereIn('status', [
                ContainerStatus::IN_TRANSIT->value,
                ContainerStatus::REACHED_DESTINATION->value,
                ContainerStatus::ARRIVED_PRIMARY_WAREHOUSE->value,
                    ])
                ->with(['branch:id,name', 'hbl_packages.hbl'])
                ->whereNotNull('unloading_started_at');

            // Apply date filters (based on unloading/arrival date)
            if ($request->filled('date_from')) {
                $query->whereDate('unloading_started_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('unloading_started_at', '<=', $request->date_to);
            }

            // Apply branch filter
            if ($request->filled('branch_id')) {
                $query->where('branch_id', $request->branch_id);
            }

            // Get all containers
            $containers = $query->get();

            // Group by agent/branch
            $agentData = [];

            foreach ($containers as $container) {
                $agentName = $container->branch->name ?? 'Unknown Agent';

                if (!isset($agentData[$agentName])) {
                    $agentData[$agentName] = [
                        'agent_name' => $agentName,
                        'total_cbm' => 0,
                        'wooden_boxes' => 0,
                        'steel_trunk' => 0,
                        'other_packages' => 0,
                        'total_packages' => 0,
                        'packages_consignees' => 0,
                        'total_consignees' => 0,
                        'containers_40ft' => 0,
                        'containers_20ft' => 0,
                        'containers_45ft' => 0,
                        'consignees' => [],
                    ];
                }

                // Count container types
                if ($container->container_type === '40FT High Cube') {
                    $agentData[$agentName]['containers_40ft']++;
                } elseif ($container->container_type === '20FT General') {
                    $agentData[$agentName]['containers_20ft']++;
                } elseif ($container->container_type === '45FT High Cube') {
                    $agentData[$agentName]['containers_45ft']++;
                }

                // Process HBL packages
                foreach ($container->hbl_packages as $package) {

                    $agentData[$agentName]['total_cbm'] += (float) ($package->volume ?? 0);
                    $agentData[$agentName]['total_packages']++;

                    // Count package types
                    $packageType = strtolower($package->package_type ?? '');
                    if (str_contains($packageType, 'wooden') || str_contains($packageType, 'box')) {
                        $agentData[$agentName]['wooden_boxes']++;
                    } elseif (str_contains($packageType, 'steel') || str_contains($packageType, 'trunk')) {
                        $agentData[$agentName]['steel_trunk']++;
                    } else {
                        $agentData[$agentName]['other_packages']++;
                    }

                    // Add consignee from HBL
                    if ($package->hbl && $package->hbl->consignee_name) {
                        $agentData[$agentName]['consignees'][$package->hbl->consignee_name] = true;
                    }
                }

                // Count unique consignees
                $agentData[$agentName]['total_consignees'] = count($agentData[$agentName]['consignees']);
                $agentData[$agentName]['packages_consignees'] = $agentData[$agentName]['total_consignees'];
            }

            // Convert to array and remove consignees array
            $records = array_values(array_map(function ($data) {
                unset($data['consignees']);
                return $data;
            }, $agentData));

            // Apply search filter
            if ($request->filled('search')) {
                $search = strtolower($request->search);
                $records = array_filter($records, function ($record) use ($search) {
                    return str_contains(strtolower($record['agent_name']), $search);
                });
                $records = array_values($records);
            }

            // Apply sorting
            $sortField = $request->get('sort_field', 'agent_name');
            $sortOrder = $request->get('sort_order', 'asc');

            usort($records, function ($a, $b) use ($sortField, $sortOrder) {
                $aVal = $a[$sortField] ?? 0;
                $bVal = $b[$sortField] ?? 0;

                if (is_numeric($aVal) && is_numeric($bVal)) {
                    $result = $aVal <=> $bVal;
                } else {
                    $result = strcasecmp($aVal, $bVal);
                }

                return $sortOrder === 'desc' ? -$result : $result;
            });

            // Calculate statistics
            $stats = [
                'total_agents' => count($records),
                'total_containers' => array_sum(array_column($records, 'containers_40ft')) +
                    array_sum(array_column($records, 'containers_20ft')) +
                    array_sum(array_column($records, 'containers_45ft')),
                'total_packages' => array_sum(array_column($records, 'total_packages')),
                'total_consignees' => array_sum(array_column($records, 'total_consignees')),
                'grand_total_cbm' => number_format(array_sum(array_column($records, 'total_cbm')), 2, '.', ''),
                'total_wooden_boxes' => array_sum(array_column($records, 'wooden_boxes')),
                'total_steel_trunk' => array_sum(array_column($records, 'steel_trunk')),
                'total_other_packages' => array_sum(array_column($records, 'other_packages')),
                'total_packages_consignees' => array_sum(array_column($records, 'packages_consignees')),
                'total_containers_40ft' => array_sum(array_column($records, 'containers_40ft')),
                'total_containers_20ft' => array_sum(array_column($records, 'containers_20ft')),
                'total_containers_45ft' => array_sum(array_column($records, 'containers_45ft')),
            ];

            // Pagination
            $page = $request->get('page', 1);
            $perPage = $request->get('per_page', 25);
            $total = count($records);
            $records = array_slice($records, ($page - 1) * $perPage, $perPage);

            return response()->json([
                'success' => true,
                'data' => $records,
                'total' => $total,
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch data: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function export(Request $request)
    {
        $format = $request->get('format', 'xlsx');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');

        $filename = 'agent-wise-container-arrival-summary-' . now()->format('Y_m_d_H_i_s');

        $export = new AgentWiseContainerArrivalSummaryExport($request->all());

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
