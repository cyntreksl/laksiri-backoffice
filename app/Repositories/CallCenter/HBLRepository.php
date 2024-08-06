<?php

namespace App\Repositories\CallCenter;

use App\Actions\HBL\GetHBLs;
use App\Actions\HBL\GetHBLsWithPackages;
use App\Factory\HBL\FilterFactory;
use App\Http\Resources\HBLResource;
use App\Interfaces\CallCenter\HBLRepositoryInterface;
use App\Interfaces\GridJsInterface;
use App\Models\HBL;
use App\Models\Token;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;

class HBLRepository implements GridJsInterface, HBLRepositoryInterface
{
    public function getHBLs()
    {
        return GetHBLs::run();
    }

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = HBL::query();

        if (! empty($search)) {
            $query->whereAny([
                'reference',
                'hbl_name',
                'contact_number',
                'consignee_name',
                'consignee_nic',
                'consignee_contact',
                'iq_number',
                'nic',
                'email',
            ], 'like', '%'.$search.'%');
        }

        //apply filters
        FilterFactory::apply($query, $filters);

        $countQuery = $query;

        $hbls = $query->orderBy($order, $direction)
            ->skip($offset)
            ->take($limit)
            ->get();

        $totalRecords = $countQuery->count();

        return response()->json([
            'data' => HBLResource::collection($hbls),
            'meta' => [
                'total' => $totalRecords,
                'page' => $offset,
                'perPage' => $limit,
                'lastPage' => ceil($totalRecords / $limit),
            ],
        ]);
    }

    public function createAndIssueToken(HBL $hbl)
    {
        // create token
        if ($hbl->consignee_id) {
            // Get the current date
            $today = Carbon::today();

            // Get the last token created today
            $lastToken = Token::whereDate('created_at', $today)->orderBy('id', 'desc')->first();

            // Determine the token value
            $tokenValue = $lastToken ? $lastToken->token + 1 : 1;

            $token = Token::create([
                'customer_id' => $hbl->consignee_id,
                'receptionist_id' => auth()->id(),
                'reference' => $hbl->reference,
                'package_count' => $hbl->packages->count(),
                'token' => $tokenValue,
            ]);

            // set customer queue
            $token->customerQueue()->create([
                'arrived_at' => now(),
            ]);

            // print token pdf
            $customPaper = [0, 0, 283.80, 567.00];

            $pdf = Pdf::loadView('pdf.customer.token', [
                'token' => $token,
            ])->setPaper($customPaper);

            $filename = 'sample'.'.pdf';

            return $pdf->stream($filename);
        }
    }

    public function getHBLsWithPackages()
    {
        return GetHBLsWithPackages::run();
    }
}
