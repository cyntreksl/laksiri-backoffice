<?php

namespace App\Http\Controllers;

use App\Repositories\WhatsappContactRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class WhatsappController extends Controller
{
    protected $whatsappContactRepository;

    public function __construct(WhatsappContactRepository $whatsappContactRepository)
    {
        $this->whatsappContactRepository = $whatsappContactRepository;
    }

    public function index()
    {
        return Inertia::render('Whatsapp/Messaging');
    }

    public function verifyWebhook(Request $request)
    {
        $verifyToken = 'LAKWPHOOK'; // Set in .env

        if ($request->hub_mode === 'subscribe' && $request->hub_verify_token === $verifyToken) {
            return response($request->hub_challenge, 200);
        }

        return response('Verification failed', 403);
    }

    public function handleWebhook(Request $request)
    {
        $data = $request->all();
        Log::info('WhatsApp Webhook Data: ', $data);

        // Check if there are messages
        if (isset($data['entry'][0]['changes'][0]['value']['messages'][0])) {
            $message = $data['entry'][0]['changes'][0]['value']['messages'][0];

            // Extract details
            $from = $message['from']; // Sender's phone number
            $text = $message['text']['body'] ?? 'No text'; // Message text

            // Process the message (you can store it, send an automated reply, etc.)
            Log::info("Message from $from: $text");

            return response()->json(['status' => 'success']);
        }

        return response()->json(['status' => 'no_message']);
    }

    public function storeContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:whatsapp_contacts,phone',
            'profile_pic' => 'nullable|url',
        ]);

        $contact = $this->whatsappContactRepository->create($validated);

        return redirect()->back()->with('success', 'Contact created successfully.');
    }
}
