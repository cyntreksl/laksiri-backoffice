<?php

namespace App\Http\Controllers;

use App\Actions\ThirdPartyShipment\GetTmpHblsBySession;
use App\Actions\ThirdPartyShipment\ImportHblFromCsv;
use App\Actions\ThirdPartyShipment\SaveThirdPartyShipment;
use App\Actions\ThirdPartyShipment\SaveThirdPartyShipmentV2;
use App\Actions\Container\CreateContainer;
use App\Actions\Container\GenerateContainerReferenceNumber;
use App\Actions\Branch\GetDestinationBranches;
use App\Enum\CargoType;
use App\Enum\ContainerType;
use App\Enum\HBLType;
use App\Http\Requests\StoreHBLRequest;
use App\Http\Requests\StoreContainerRequest;
use App\Interfaces\CountryRepositoryInterface;
use App\Interfaces\HBLRepositoryInterface;
use App\Interfaces\PackageTypeRepositoryInterface;
use App\Interfaces\AirLineRepositoryInterface;
use App\Models\AirLine;
use App\Models\Branch;
use App\Models\Container;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ThirdPartyShipmentController extends Controller
{
    public function __construct(
        private readonly CountryRepositoryInterface $countryRepository,
        private readonly PackageTypeRepositoryInterface $packageTypeRepository,
        private readonly HBLRepositoryInterface $HBLRepository,
        private readonly AirLineRepositoryInterface $airLineRepository,
    ) {}

    /**
     * Display a listing of third party shipments.
     */
    public function index()
    {
        return Inertia::render('ThirdPartyShipments/ThirdPartyShipmentIndex');
    }

    /**
     * Show the form for creating a new third party shipment.
     */
    public function create()
    {
        $agents = Branch::thirdpartyAgents()->get();
        $cargoTypes = CargoType::cases();
        $hblTypes = HBLType::cases();
        $shipments = Container::whereStatus('CONTAINER ORDERED')->get();
        $airLines = AirLine::pluck('name', 'id');
        $packageTypes = $this->packageTypeRepository->getPackageTypes();

        return Inertia::render('ThirdPartyShipments/ThirdPartyShipmentCreate',
            compact('agents', 'cargoTypes', 'hblTypes', 'shipments', 'airLines', 'packageTypes'));
    }

    public function createV2()
    {
        $agents = Branch::thirdpartyAgents()->get();
        $cargoTypes = CargoType::cases();
        $hblTypes = HBLType::cases();
        $shipments = Container::whereStatus('CONTAINER ORDERED')->get();
        $airLines = AirLine::pluck('name', 'id');
        $countryCodes = $this->countryRepository->getAllPhoneCodes();
        $containerTypes = ContainerType::getDropdownOptions();
        $seaContainerOptions = ContainerType::getSeaCargoOptions();
        $airContainerOptions = ContainerType::getAirCargoOptions();
        $warehouses = GetDestinationBranches::run()->reject(fn ($warehouse) => $warehouse->name === 'Other');
        $airLinesList = $this->airLineRepository->getAirLines();
        $packageTypes = $this->packageTypeRepository->getPackageTypes();

        return Inertia::render('ThirdPartyShipments/ThirdPartyShipmentCreateV2',
            compact('agents', 'cargoTypes', 'hblTypes', 'shipments', 'airLines', 'countryCodes', 'containerTypes', 'seaContainerOptions', 'airContainerOptions', 'warehouses', 'airLinesList', 'packageTypes'));
    }

    /**
     * Store a newly created third party shipment.
     */
    public function store(Request $request)
    {
        // This can be implemented later if needed for direct form submission
        return redirect()->route('third-party-shipments.index')
            ->with('success', 'Third party shipment created successfully.');
    }

    /**
     * Display the specified third party shipment.
     */
    public function show($id)
    {
        return Inertia::render('ThirdPartyShipments/ThirdPartyShipmentShow', [
            'shipment_id' => $id,
        ]);
    }

    /**
     * Show the form for editing the specified third party shipment.
     */
    public function edit($id)
    {
        return Inertia::render('ThirdPartyShipments/ThirdPartyShipmentEdit', [
            'shipment_id' => $id,
        ]);
    }

    /**
     * Update the specified third party shipment.
     */
    public function update(Request $request, $id)
    {
        // This can be implemented later if needed
        return redirect()->route('third-party-shipments.index')
            ->with('success', 'Third party shipment updated successfully.');
    }

    /**
     * Remove the specified third party shipment.
     */
    public function destroy($id)
    {
        // This can be implemented later if needed
        return redirect()->route('third-party-shipments.index')
            ->with('success', 'Third party shipment deleted successfully.');
    }

    public function importCsv(Request $request): JsonResponse
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        try {
            $result = ImportHblFromCsv::run($request->file('csv_file'));

            if (! $result['success']) {
                return response()->json([
                    'success' => false,
                    'message' => 'CSV import failed',
                    'errors' => $result['errors'],
                ], 422);
            }

            return response()->json([
                'success' => true,
                'message' => "Successfully imported {$result['hbls_count']} HBLs with {$result['total_packages']} packages",
                'session_id' => $result['session_id'],
                'hbls_count' => $result['hbls_count'],
                'total_packages' => $result['total_packages'],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred during import: '.$e->getMessage(),
            ], 500);
        }
    }

    public function getTmpHbls(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
        ]);

        try {
            $hbls = GetTmpHblsBySession::run($request->get('session_id'));

            return response()->json([
                'success' => true,
                'hbls' => $hbls,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve HBL data: '.$e->getMessage(),
            ], 500);
        }
    }

    public function saveShipment(Request $request): JsonResponse
    {
        $request->validate([
            'session_id' => 'required|string',
            'cargo_type' => 'required|string',
            'hbl_type' => 'required|string',
            'agent' => 'required|integer',
            'shipment' => 'required|integer',
        ]);

        try {
            $result = SaveThirdPartyShipment::run($request->session_id, $request->all());

            return response()->json([
                'success' => true,
                'message' => $result['message'],
                'hbls_created' => $result['hbls_created'],
                'hbl_ids' => $result['hbl_ids'],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to save shipment: '.$e->getMessage(),
            ], 500);
        }
    }

    public function downloadSample(): \Symfony\Component\HttpFoundation\BinaryFileResponse
    {
        $filePath = public_path('third-party-hbl-import-sample.csv');

        if (! file_exists($filePath)) {
            abort(404, 'Sample file not found');
        }

        return response()->download($filePath, 'third-party-hbl-import-sample.csv');
    }

    public function multiOptions()
    {
        return Inertia::render('ThirdPartyShipments/MultiOptionLayout');
    }

    public function saveShipmentV2(StoreHBLRequest $request)
    {
        SaveThirdPartyShipmentV2::run($request->all());
    }

    public function createContainer(StoreContainerRequest $request)
    {
        try {
            $container = CreateContainer::run($request->all());

            return back()->with([
                'flash' => [
                    'success' => true,
                    'message' => 'Container created successfully',
                    'container_id' => $container->id,
                    'container_reference' => $container->reference,
                    'container_cargo_type' => $container->cargo_type,
                ],
            ]);
        } catch (\Exception $e) {
            return back()->withErrors([
                'container' => 'Failed to create container: '.$e->getMessage(),
            ]);
        }
    }

    public function generateContainerReference(): JsonResponse
    {
        try {
            $reference = GenerateContainerReferenceNumber::run();

            return response()->json([
                'success' => true,
                'reference' => $reference,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate reference: '.$e->getMessage(),
            ], 500);
        }
    }
}
