<?php

namespace App\Http\Controllers\Finance;

use App\Http\Controllers\Controller;
use App\Http\Requests\CompletePaymentRequest;
use App\Interfaces\Finance\ContainerPaymentRepositoryInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ContainerPaymentController extends Controller
{
    public function __construct(
        private readonly ContainerPaymentRepositoryInterface $containerPaymentRepository,
    ) {}

    public function index()
    {
        return Inertia::render('Finance/ContainerPayment/FinanceContainerPaymentRequestsList');
    }

    public function list(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate', 'toDate', 'cargoMode', 'drivers', 'officers', 'paymentStatus', 'deliveryType', 'warehouse']);

        return $this->containerPaymentRepository->dataset($limit, $page, $order, $dir, $search, $filters);
    }

    public function approveContainerPayments(Request $request)
    {
        $this->containerPaymentRepository->approveContainerPayments($request['data']['container_payments_ids']);
    }

    public function approvedContainerPayments()
    {
        return Inertia::render('Finance/ContainerPayment/ApprovedContainerPaymentsList');
    }

    public function approvedList(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate', 'toDate', 'cargoMode', 'drivers', 'officers', 'paymentStatus', 'deliveryType', 'warehouse']);

        return $this->containerPaymentRepository->approvedPaymentsDataset($limit, $page, $order, $dir, $search, $filters);
    }

    public function revokeContainerPaymentsApprovals(Request $request)
    {
        $this->containerPaymentRepository->revokeContainerPaymentsApprovals($request['data']['container_payments_ids']);
    }

    public function completePayment(CompletePaymentRequest $request)
    {
        $this->containerPaymentRepository->completePayments($request->all());
    }
}
