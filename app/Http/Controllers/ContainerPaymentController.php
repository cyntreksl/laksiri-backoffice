<?php

namespace App\Http\Controllers;

use App\Interfaces\ContainerPaymentRepositoryInterface;
use App\Models\Container;
use App\Models\ContainerPayment;
use Illuminate\Http\Request;
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
}
