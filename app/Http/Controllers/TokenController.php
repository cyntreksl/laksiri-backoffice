<?php

namespace App\Http\Controllers;

use App\Interfaces\TokenRepositoryInterface;
use App\Models\ReceptionVerification;
use App\Models\Token;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TokenController extends Controller
{
    public function __construct(
        private readonly TokenRepositoryInterface $tokenRepository,
    ) {}

    public function index()
    {
        return Inertia::render('Token/Tokens');
    }

    public function list(Request $request)
    {
        $limit = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $order = $request->input('sort_field', 'id');
        $dir = $request->input('sort_order', 'asc');
        $search = $request->input('search', null);

        $filters = $request->only(['fromDate', 'toDate', 'cargoMode', 'deliveryType', 'status']);

        return $this->tokenRepository->dataset($limit, $page, $order, $dir, $search, $filters);
    }

    public function show(Token $token)
    {
        $hbl = $token->hbl;

        $documents = ReceptionVerification::reception_verification_documents();

        if ($hbl->hbl_type === 'UPB') {
            $documents = array_filter($documents, fn ($doc) => $doc !== 'NIC');
        } else {
            $documents = array_filter($documents, fn ($doc) => $doc !== 'Passport');
        }

        // Get all queue logs for this token
        $queueLogs = $token->queueLogs;

        return Inertia::render('Token/ShowToken', [
            'token' => $token,
            'hblId' => $token->hbl_id,
            'verificationDocuments' => $documents,
            'customerQueue' => $token->customerQueue,
            'queueLogs' => $queueLogs,
        ]);
    }
}
