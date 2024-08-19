<?php

namespace App\Actions\HBL\HBLPayment;

use App\Models\HBL;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPaymentByReference
{
    use AsAction;

    public function handle(string $reference): JsonResponse
    {
        $hbl = HBL::where('reference', $reference)->firstOrFail();

        $payments = $hbl->hblPayment;

        return response()->json($payments);
    }
}
