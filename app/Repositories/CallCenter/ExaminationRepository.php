<?php

namespace App\Repositories\CallCenter;

use App\Actions\CustomerFeedback\SendFeedbackMail;
use App\Actions\Examination\CreateExamination;
use App\Http\Resources\CallCenter\ExaminationCollection;
use App\Interfaces\CallCenter\ExaminationRepositoryInterface;
use App\Models\CustomerQueue;
use App\Models\HBL;
use Illuminate\Support\Facades\DB;

class ExaminationRepository implements ExaminationRepositoryInterface
{
    public function releaseHBL(array $data): void
    {
        try {
            DB::beginTransaction();

            $hbl = HBL::withoutGlobalScopes()->where('reference', $data['customer_queue']['token']['reference'])->first();

            // release packages
            $examination = CreateExamination::run($data, $hbl->id);

            $customerQueue = CustomerQueue::find($data['customer_queue']['id']);

            if ($customerQueue) {
                $customerQueue->update([
                    'left_at' => now(),
                ]);

                // set queue status log
                $customerQueue->addQueueStatus(
                    CustomerQueue::EXAMINATION_QUEUE,
                    $customerQueue->token->customer_id,
                    $customerQueue->token_id,
                    null,
                    now(),
                );
            }

            // mark as released HBL

            $countReleasedPackages = count(array_filter($examination->released_packages, fn ($value) => $value === true));
            $countHBLPackages = $hbl->packages->count();

            if ($countHBLPackages === $countReleasedPackages) {
                $hbl->is_released = true;
                $hbl->save();
            }
            // generate gate pass with QR

            // update examinations table as an is_issued_gate_pass is true

            //send mail after 30min
            $emailData = [
                'customerId' => $data['customer_queue']['token']['customer_id'],
                'hblId' => $hbl->id,
                'tokenId' => $data['customer_queue']['token_id'],
            ];
            SendFeedbackMail::run($emailData);

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw new \Exception('Failed to create examination record when releasing hbl packages: '.$e->getMessage());
        }
    }

    public function dataset(int $limit = 10, int $offset = 0, string $order = 'id', string $direction = 'asc', ?string $search = null, array $filters = [])
    {
        $query = CustomerQueue::query()
            ->examinationQueue()
            ->whereNotNull('left_at')
            ->has('token.verification');

        $records = $query->orderBy($order, $direction)
            ->skip($offset)
            ->take($limit)
            ->get();

        $totalRecords = $query->count();

        return response()->json([
            'data' => ExaminationCollection::collection($records),
            'meta' => [
                'total' => $totalRecords,
                'page' => $offset,
                'perPage' => $limit,
                'lastPage' => ceil($totalRecords / $limit),
            ],
        ]);
    }
}
