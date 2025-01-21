<?php

namespace App\Repositories\CallCenter;

use App\Actions\HBL\GetHBLs;
use App\Actions\HBL\GetHBLsWithPackages;
use App\Enum\HBLType;
use App\Factory\HBL\FilterFactory;
use App\Http\Resources\HBLResource;
use App\Interfaces\CallCenter\HBLRepositoryInterface;
use App\Interfaces\GridJsInterface;
use App\Models\CustomerQueue;
use App\Models\HBL;
use App\Models\Scopes\BranchScope;
use App\Models\Token;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class HBLRepository implements GridJsInterface, HBLRepositoryInterface
{
    public function getHBLs()
    {
        return GetHBLs::run();
    }

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = HBL::query()->withoutGlobalScope(BranchScope::class)->with('tokens.customerQueue');

        if (! empty($search)) {
            $query->whereAny([
                'reference',
                'hbl_number',
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
        $totalRecords = $countQuery->count();

        $hbls = $query->orderBy($order, $direction)
            ->skip($offset)
            ->take($limit)
            ->get();

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

            // Check if any tokens exist for today
            $tokensExistToday = Token::whereDate('created_at', $today)->exists();

            if (! $tokensExistToday) {
                DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                DB::table('tokens')->delete();
                DB::table('customer_queues')->delete();
                DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            }

            // Get the last token created today
            $lastToken = Token::whereDate('created_at', $today)->orderBy('id', 'desc')->first();

            // Determine the token value
            $tokenValue = $lastToken ? $lastToken->token + 1 : 1;

            $token = Token::create([
                'hbl_id' => $hbl->id,
                'customer_id' => $hbl->consignee_id,
                'receptionist_id' => auth()->id(),
                'reference' => $hbl->reference,
                'package_count' => $hbl->packages->count(),
                'token' => $tokenValue,
            ]);

            // set customer queue
            $token->customerQueue()->create([
                'type' => CustomerQueue::DOCUMENT_VERIFICATION_QUEUE,
            ]);

            // set queue status log
            $hbl->addQueueStatus(
                CustomerQueue::TOKEN_ISSUED,
                $hbl->consignee_id,
                $token->id,
                date('Y-m-d H:i:s', (time() - 60)),
                now(),
            );

            // print token pdf
            $customPaper = [0, 0, 283.80, 567.00];

            $pdf = Pdf::loadView('pdf.customer.token', [
                'token' => $token->load(['hbl' => function ($query) {
                    $query->withoutGlobalScope(BranchScope::class);
                }]),
            ])->setPaper($customPaper);

            $filename = $hbl->hbl_number.'.pdf';

            return $pdf->stream($filename);
        }
    }

    public function getHBLsWithPackages()
    {
        return GetHBLsWithPackages::run();
    }

    public function getDoorToDoorHBL(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = HBL::query()
            ->where('hbl_type', '=', HBLType::DOOR_TO_DOOR->value)
            ->where('system_status', '>=', 4.3)
            ->where('is_released', '=', 0)
            ->where('is_driver_assigned', '=', 0)
            ->where(function ($query) {
                $query->where('status', '=', 'reached')
                    ->orWhereNull('status');
            });

        if (! empty($search)) {
            $query->whereAny([
                'reference',
                'hbl_number',
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
        $totalRecords = $countQuery->count();

        $hbls = $query->orderBy($order, $direction)
            ->skip($offset)
            ->take($limit)
            ->get();

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
}
