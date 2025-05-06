<?php

namespace App\Http\Controllers;

use App\Interfaces\ContainerPaymentRepositoryInterface;
use App\Models\Container;
use Illuminate\Http\Request;

class ContainerPaymentController extends Controller
{
    public function __construct(
        private readonly ContainerPaymentRepositoryInterface $containerPaymentRepository,
    ) {}

    public function store(Request $request)
    {
        $this->containerPaymentRepository->store($request->all());
    }

    public function getContainerPayment(Container $container)
    {
        return $this->containerPaymentRepository->getContainerPayment($container);
    }
}
