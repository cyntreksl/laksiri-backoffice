<?php

namespace App\Http\Controllers;

use App\Actions\BondStorage\GenerateBondStorageNumbers;
use App\Actions\BondStorage\GetShipmentPackages;
use App\Models\Container;
use App\Models\HBL;
use App\Models\HBLPackage;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class BondStorageNumberController extends Controller
{
    public function index()
    {
        $containers = Container::with(['hbl_packages.hbl'])
            ->whereIn('status', ['REACHED DESTINATION', 'UNLOADED'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($container) {
                $packageCount = $container->hbl_packages->count();
                $packagesWithoutBond = $container->hbl_packages->filter(function($pkg) {
                    return empty($pkg->bond_storage_number);
                })->count();
                
                return [
                    'id' => $container->id,
                    'reference' => $container->reference,
                    'cargo_type' => $container->cargo_type,
                    'container_type' => $container->container_type,
                    'status' => $container->status,
                    'estimated_time_of_arrival' => $container->estimated_time_of_arrival,
                    'total_packages' => $packageCount,
                    'packages_without_bond' => $packagesWithoutBond,
                ];
            });

        $settings = Setting::first();

        return Inertia::render('BondStorage/Index', [
            'containers' => $containers,
            'settings' => [
                'bond_storage_sea_serial' => $settings->bond_storage_sea_serial ?? 1,
                'bond_storage_air_serial' => $settings->bond_storage_air_serial ?? 1,
                'auto_bond_generation_enabled' => $settings->auto_bond_generation_enabled ?? false,
            ],
        ]);
    }

    public function getShipmentPackages(Request $request)
    {
        $validated = $request->validate([
            'container_id' => 'required|exists:containers,id',
        ]);

        try {
            $packages = GetShipmentPackages::run($validated['container_id']);

            return response()->json([
                'success' => true,
                'data' => $packages,
                'debug' => [
                    'total_packages' => $packages['total_packages'] ?? 0,
                    'total_hbl_groups' => count($packages['hbl_groups'] ?? []),
                ]
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to get shipment packages', [
                'container_id' => $validated['container_id'],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load shipment packages: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function generate(Request $request)
    {
        $validated = $request->validate([
            'container_id' => 'required|exists:containers,id',
            'packages' => 'required|array',
            'packages.*.id' => 'required|exists:hbl_packages,id',
            'packages.*.hbl_id' => 'required|exists:hbl,id',
            'manual_hbls' => 'nullable|array',
            'manual_hbls.*.hbl_number' => 'required|string',
            'manual_hbls.*.packages' => 'required|array',
        ]);

        try {
            DB::beginTransaction();

            $result = GenerateBondStorageNumbers::run(
                $validated['container_id'],
                $validated['packages'],
                $validated['manual_hbls'] ?? []
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bond storage numbers generated successfully',
                'data' => $result,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate bond storage numbers: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function updateSettings(Request $request)
    {
        $validated = $request->validate([
            'bond_storage_sea_serial' => 'required|integer|min:1',
            'bond_storage_air_serial' => 'required|integer|min:1',
            'auto_bond_generation_enabled' => 'required|boolean',
        ]);

        $settings = Setting::first();
        $settings->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Settings updated successfully',
        ]);
    }

    public function searchHbl(Request $request)
    {
        $validated = $request->validate([
            'hbl_number' => 'required|string',
        ]);

        $hbl = HBL::where('hbl_number', $validated['hbl_number'])
            ->with(['packages' => function ($query) {
                $query->whereNull('bond_storage_number');
            }])
            ->first();

        if (!$hbl) {
            return response()->json([
                'success' => false,
                'message' => 'HBL not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $hbl->id,
                'hbl_number' => $hbl->hbl_number,
                'packages' => $hbl->packages->map(function ($package) {
                    return [
                        'id' => $package->id,
                        'package_type' => $package->package_type,
                        'quantity' => $package->quantity,
                        'weight' => $package->weight,
                        'volume' => $package->volume,
                    ];
                }),
            ],
        ]);
    }
}
