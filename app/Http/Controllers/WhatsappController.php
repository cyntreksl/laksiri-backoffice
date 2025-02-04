<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsappController extends Controller
{
    public function webhook(Request $request)
    {
        $data = $request->all();
        Log::info('Whatsapp Webhook', $data);

        return response()->json(['status' => 'success']);
    }
}
