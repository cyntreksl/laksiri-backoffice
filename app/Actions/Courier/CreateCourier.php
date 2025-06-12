<?php

namespace App\Actions\Courier;

use App\Actions\HBL\GenerateCourierNumber;
use App\Actions\User\GetUserCurrentBranchID;
use App\Models\Courier;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateCourier
{
    use AsAction;

    public function handle(array $data)
    {
        $originalAmount = (float) ($data['amount'] ?? 0.00);

        // Calculate discount on original amount
        $discountAmount = $this->calculateValue($originalAmount, $data['discount_method'] ?? null, $data['discount_value'] ?? null);

        // Calculate discounted amount
        $discountedAmount = $originalAmount - $discountAmount;

        // Calculate tax on discounted amount
        $taxAmount = $this->calculateValue($discountedAmount, $data['tax_method'] ?? null, $data['tax_value'] ?? null);

        // Calculate grand total
        $grandTotal = $this->calculateGrandTotal(
            $originalAmount,
            $taxAmount,
            $discountAmount
        );

        $courier = Courier::create([
            'branch_id' => GetUserCurrentBranchID::run(),
            'cargo_type' => $data['cargo_type'],
            'hbl_type' => $data['hbl_type'],
            'courier_agent' => $data['courier_agent'],
            'courier_number' => GenerateCourierNumber::run(GetUserCurrentBranchID::run()),
            'name' => $data['name'],
            'email' => $data['email'],
            'contact_number' => $data['contact_number'],
            'nic' => $data['nic'],
            'iq_number' => $data['iq_number'],
            'address' => $data['address'],
            'consignee_name' => $data['consignee_name'],
            'consignee_nic' => $data['consignee_nic'],
            'consignee_contact' => $data['consignee_contact'],
            'consignee_address' => $data['consignee_address'],
            'consignee_note' => $data['consignee_note'],
            'status' => $data['status'],
            'amount' => $originalAmount,
            'discount_amount' => $discountAmount,
            'tax_amount' => $taxAmount,
            'grand_total' => $grandTotal,
            'tax_method' => $data['tax_method'] ?? null,
            'tax_value' => $data['tax_value'] ?? null,
            'discount_method' => $data['discount_method'] ?? null,
            'discount_value' => $data['discount_value'] ?? null,
            'created_by' => auth()->id(),
        ]);

        return $courier;
    }

    private function calculateValue(float $amount, ?string $method, $value): float
    {
        if (empty($value) || empty($method) || !is_numeric($value) || $value <= 0) {
            return 0.00;
        }

        $value = (float) $value;

        if ($method === 'Percentage') {
            return ($amount * $value) / 100;
        } elseif ($method === 'Fixed') {
            return $value;
        }

        return 0.00;
    }


    private function calculateGrandTotal($amount, $taxAmount, $discountAmount)
    {
        return $amount + $taxAmount - $discountAmount;
    }
}
