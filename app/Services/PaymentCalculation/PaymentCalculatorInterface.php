<?php

namespace App\Services\PaymentCalculation;

interface PaymentCalculatorInterface
{
    public function calculate(PaymentCalculationRequest $request): array;
}
