<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompletePaymentRequest;
use App\Interfaces\ContainerPaymentRepositoryInterface;
use App\Models\Container;
use App\Models\ContainerPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ContainerPaymentController extends Controller
{
    public function __construct(
        private readonly ContainerPaymentRepositoryInterface $containerPaymentRepository,
    ) {}

    public function index()
    {
        return Inertia::render('ContainerPayment/ContainerPaymentList');
    }

    public function list(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate']);

        return $this->containerPaymentRepository->dataset($limit, $page, $order, $dir, $search, $filters);
    }

    public function store(Request $request)
    {
        $this->containerPaymentRepository->store($request->all());
    }

    public function getContainerPayment(Container $container)
    {
        return $this->containerPaymentRepository->getContainerPayment($container);
    }

    public function destroy(ContainerPayment $containerPayment)
    {
        return $this->containerPaymentRepository->delete($containerPayment);
    }

    public function showContainerPaymentRefund()
    {
        return Inertia::render('ContainerPayment/ContainerPaymentRefundList');
    }

    public function refundList(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate']);

        return $this->containerPaymentRepository->refundDataset($limit, $page, $order, $dir, $search, $filters);
    }

    public function markRefundAsCollected(Request $request)
    {
        $this->containerPaymentRepository->markRefundCollection($request['data']['container_payments_ids']);
    }

    public function showCompletedContainerPayment()
    {
        return Inertia::render('ContainerPayment/ContainerPaymentCompletedList');
    }

    public function completedList(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate']);

        return $this->containerPaymentRepository->completedDataset($limit, $page, $order, $dir, $search, $filters);
    }

    public function paymentRequestList()
    {
        return Inertia::render('ContainerPayment/RequestsList');
    }

    public function requestList(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate']);

        return $this->containerPaymentRepository->requestDataset($limit, $page, $order, $dir, $search, $filters);
    }

    public function approveContainerPayments(Request $request)
    {
        $this->containerPaymentRepository->approveContainerPayments($request['data']['container_payments_ids']);
    }

    public function approvedContainerPayments()
    {
        return Inertia::render('ContainerPayment/ApprovedList');
    }

    public function approvedList(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate']);

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

    public function approveSingle(Request $request)
    {
        $payment = ContainerPayment::findOrFail($request->container_payment_id);

        $approvedField = $request->charge_type.'_finance_approved';
        $approvedAtField = $request->charge_type.'_approved_at';
        $approvedByField = $request->charge_type.'_approved_by';

        if (! isset($payment[$approvedField])) {
            return response()->json(['error' => 'Invalid charge type'], 400);
        }

        $payment[$approvedField] = true;
        $payment[$approvedAtField] = now();
        $payment[$approvedByField] = Auth::user()->id;
        $payment->save();
    }

    public function revokeSingle(Request $request)
    {
        $payment = ContainerPayment::findOrFail($request->container_payment_id);

        $approvedField = $request->charge_type.'_finance_approved';
        $approvedAtField = $request->charge_type.'_approved_at';
        $approvedByField = $request->charge_type.'_approved_by';

        if (! isset($payment[$approvedField])) {
            return response()->json(['error' => 'Invalid charge type'], 400);
        }

        $payment[$approvedField] = false;
        $payment[$approvedAtField] = null;
        $payment[$approvedByField] = null;
        $payment->save();
    }
}
