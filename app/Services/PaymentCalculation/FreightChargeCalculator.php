<?php

namespace App\Services\PaymentCalculation;

use Illuminate\Database\Eloquent\Collection;

class FreightChargeCalculator
{
    public function calculate(array $operations, Collection $latestPriceRules, float $grandTotalQuantity): array
    {
        $freightCharge = 0.0;
        $freightChargeOperations = [];
        $currentQuantity = $grandTotalQuantity;
        $lastOperator = '';
        $lastValue = 0.0;

        foreach ($operations as $operation) {
            $operationQuantity = (float) filter_var($operation, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $priceRule = $latestPriceRules[$operation];

            $calculation = $this->parseTrueAction($priceRule->true_action);
            $quantityAfterOperation = $currentQuantity - $operationQuantity;

            $result = $this->applyOperation(
                $calculation['operator'],
                $calculation['value'],
                $quantityAfterOperation
            );

            if (isset($result['error'])) {
                return $result;
            }

            $freightCharge += $result['value'];
            $freightChargeOperations[] = "{$quantityAfterOperation} " .
                ($calculation['operator'] !== '' ? $calculation['operator'] : '=>') .
                ' ' . number_format((float) $calculation['value'], 2);

            $currentQuantity = $operationQuantity;
            $lastOperator = $calculation['operator'];
            $lastValue = $calculation['value'];
        }

        return [
            'freight_charge' => $freightCharge,
            'freight_charge_operations' => $freightChargeOperations,
            'per_freight_charge' => $lastValue,
            'freight_operator' => $lastOperator,
        ];
    }

    private function parseTrueAction(string $trueAction): array
    {
        preg_match('/^([*+\-\/]?)(\d+(?:\.\d+)?)/', trim($trueAction), $matches);

        return [
            'operator' => $matches[1] ?? '',
            'value' => floatval($matches[2] ?? 0)
        ];
    }

    private function applyOperation(string $operator, float $value, float $quantity): array
    {
        switch ($operator) {
            case '*':
                return ['value' => $quantity * $value];
            case '+':
                return ['value' => $quantity + $value];
            case '-':
                return ['value' => $quantity - $value];
            case '/':
                if ($value == 0) {
                    return ['error' => 'Division by zero error'];
                }
                return ['value' => $quantity / $value];
            case '':
                return ['value' => $value];
            default:
                return ['error' => 'Unsupported operation'];
        }
    }
}
