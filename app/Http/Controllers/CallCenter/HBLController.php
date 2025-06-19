<?php

namespace App\Http\Controllers\CallCenter;

use App\Actions\Branch\GetDestinationBranches;
use App\Actions\HBL\GetHBLByIdWithPackages;
use App\Enum\HBLPaymentStatus;
use App\Http\Controllers\Controller;
use App\Interfaces\CallCenter\HBLRepositoryInterface;
use App\Interfaces\DriverRepositoryInterface;
use App\Interfaces\PriceRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class HBLController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private readonly HBLRepositoryInterface $HBLRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly PriceRepositoryInterface $priceRepository,
        private readonly DriverRepositoryInterface $driverRepository,
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        if (! auth()->user()->canAny(['hbls.index', 'customer-queue.issue token'])) {
            abort(403, 'Unauthorized');
        }

        return Inertia::render('CallCenter/HBL/HBLList', [
            'users' => $this->userRepository->getUsers(['customer']),
            'hbls' => $this->HBLRepository->getHBLsWithPackages(),
            'paymentStatus' => HBLPaymentStatus::cases(),
            'warehouses' => GetDestinationBranches::run(),
        ]);
    }

    public function receptionIndex()
    {
        return Inertia::render('CallCenter/HBL/CallCenterHBLList', [
            'users' => $this->userRepository->getUsers(['customer']),
            'hbls' => $this->HBLRepository->getHBLsWithPackages(),
            'paymentStatus' => HBLPaymentStatus::cases(),
            'warehouses' => GetDestinationBranches::run(),
        ]);
    }

    /**
     * Display appointment list
     */
    public function appointmentList()
    {
        $this->authorize('hbls.index');

        return Inertia::render('CallCenter/HBL/AppointmentList', [
            'users' => $this->userRepository->getUsers(['customer']),
            'hbls' => $this->HBLRepository->getHBLsWithPackages(),
            'paymentStatus' => HBLPaymentStatus::cases(),
            'warehouses' => GetDestinationBranches::run(),
        ]);
    }

    /**
     * Display follow-up list
     */
    public function followupList()
    {
        $this->authorize('hbls.index');

        return Inertia::render('CallCenter/HBL/FollowupList', [
            'users' => $this->userRepository->getUsers(['customer']),
            'hbls' => $this->HBLRepository->getHBLsWithPackages(),
            'paymentStatus' => HBLPaymentStatus::cases(),
            'warehouses' => GetDestinationBranches::run(),
        ]);
    }

    public function list(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate', 'toDate', 'cargoMode', 'createdBy', 'deliveryType', 'warehouse', 'isHold', 'paymentStatus', 'isDelayed', 'drivers', 'officers']);

        return $this->HBLRepository->dataset($limit, $page, $order, $dir, $search, $filters);
    }

    public function createToken($hbl)
    {
        $hbl = GetHBLByIdWithPackages::run($hbl);

        return $this->HBLRepository->createAndIssueToken($hbl);
    }

    public function createTokenWithVerification(Request $request, $hbl)
    {
        $request->validate([
            'is_checked' => 'nullable|array',
            'note' => 'nullable|string|max:1000',
        ]);

        $hbl = GetHBLByIdWithPackages::run($hbl);

        $result = $this->HBLRepository->createAndIssueTokenWithVerification($hbl, $request->only(['is_checked', 'note']));

        // If it's an Inertia request, return the token data without redirect
        if ($request->header('X-Inertia')) {
            $resultData = $result->getData();

            return response()->json([
                'success' => true,
                'message' => 'Token issued successfully!',
                'token' => $resultData->token ?? null,
                'download_url' => route('call-center.hbls.download-token', $resultData->token->id ?? ''),
                'print_url' => route('call-center.hbls.print-token', $resultData->token->id ?? ''),
                'hbl' => [
                    'hbl_number' => $hbl->hbl_number,
                    'hbl_name' => $hbl->hbl_name,
                    'reference' => $hbl->reference,
                    'consignee_name' => $hbl->consignee_name,
                ],
            ]);
        }

        // For non-Inertia requests, return original JSON
        return $result;
    }

    public function showDoorToDoorList()
    {
        $this->authorize('hbls.index');

        return Inertia::render('CallCenter/HBL/DoorToDoorList', [
            'drivers' => $this->driverRepository->getAllDrivers(),
            'paymentStatus' => HBLPaymentStatus::cases(),
            'users' => $this->userRepository->getUsers(['customer']),
        ]);
    }

    public function getDoorToDoorList(Request $request): JsonResponse
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate', 'toDate', 'cargoMode', 'isHold', 'drivers', 'officers', 'paymentStatus', 'warehouse']);

        return $this->HBLRepository->getDoorToDoorHBL($limit, $page, $order, $dir, $search, $filters);
    }

    public function downloadToken($tokenId)
    {
        return $this->HBLRepository->generateTokenPDF($tokenId, 'download');
    }

    public function printToken($tokenId)
    {
        return $this->HBLRepository->generateTokenPDF($tokenId, 'print');
    }
}
