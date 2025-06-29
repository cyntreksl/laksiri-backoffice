<?php

namespace Tests\Unit;

use App\Services\PaymentCalculation\PaymentCalculationRequest;
use App\Services\PaymentCalculation\PaymentCalculationResult;
use App\Services\PaymentCalculation\PackagePaymentCalculator;
use App\Services\PaymentCalculation\FreightChargeCalculator;
use App\Services\PaymentCalculation\PriceRuleProcessor;
use App\Services\PaymentCalculation\StandardPaymentCalculator;
use Tests\TestCase;

class PaymentCalculationTest extends TestCase
{
    public function test_payment_calculation_request_creates_correctly()
    {
        $request = new PaymentCalculationRequest(
            cargoType: 'Sea Cargo',
            hblType: 'Import',
            grandTotalVolume: 10.5,
            grandTotalWeight: 5.2,
            packageListLength: 2,
            destinationBranch: 1,
            isActivePackage: true,
            packageList: [['package_rule' => 1, 'quantity' => 2]]
        );

        $this->assertEquals(10.5, $request->getGrandTotalQuantity());
        $this->assertTrue($request->hasPackages());
        $this->assertFalse($request->isEmpty());
    }

    public function test_payment_calculation_result_formats_correctly()
    {
        $result = new PaymentCalculationResult(
            freightCharge: 100.50,
            billCharge: 25.75,
            otherCharge: 15.25,
            packageCharges: 10.00,
            destinationCharges: 5.25,
            isEditable: true,
            vat: 0.15,
            perPackageCharge: 5.00,
            perVolumeCharge: 2.50,
            perFreightCharge: 10.05,
            freightOperator: '*',
            priceMode: 'Standard',
            grandTotalWithoutDiscount: 141.50,
            freightChargeOperations: ['10.5 * 10.05']
        );

        $array = $result->toArray();

        $this->assertEquals('100.500', $array['freight_charge']);
        $this->assertEquals('25.750', $array['bill_charge']);
        $this->assertEquals('15.25', $array['other_charge']);
        $this->assertTrue($array['is_editable']);
        $this->assertEquals(0.15, $array['vat']);
        $this->assertEquals('Standard', $array['price_mode']);
    }

    public function test_payment_calculation_result_creates_error_correctly()
    {
        $result = PaymentCalculationResult::createError('Test error message');

        $this->assertEquals('Test error message', $result->error);
    }

    public function test_payment_calculation_result_creates_empty_correctly()
    {
        $result = PaymentCalculationResult::createEmpty();

        $this->assertTrue($result->isEditable);
        $this->assertEquals('Package', $result->priceMode);
    }

    public function test_freight_charge_calculator_parses_operations_correctly()
    {
        $calculator = new FreightChargeCalculator();

        $reflection = new \ReflectionClass($calculator);
        $method = $reflection->getMethod('parseTrueAction');
        $method->setAccessible(true);

        $result = $method->invoke($calculator, '*10.5');

        $this->assertEquals('*', $result['operator']);
        $this->assertEquals(10.5, $result['value']);
    }

    public function test_price_rule_processor_sorts_operations_correctly()
    {
        $processor = new PriceRuleProcessor();

        $mockCollection = \Mockery::mock(\Illuminate\Database\Eloquent\Collection::class);
        $mockCollection->shouldReceive('toArray')
            ->andReturn(['>10' => (object)['condition' => '>10'], '>5' => (object)['condition' => '>5']]);

        $operations = $processor->getSortedOperations($mockCollection);

        $this->assertEquals(['>10', '>5'], $operations);
    }
}
