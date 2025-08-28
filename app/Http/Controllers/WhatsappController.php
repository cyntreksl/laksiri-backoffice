<?php

namespace App\Http\Controllers;

use App\Http\Requests\SendWhatsappMessageRequest;
use App\Models\WhatsappContact;
use App\Repositories\WhatsappContactRepository;
use App\Services\WhatsAppService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class WhatsappController extends Controller
{
    protected $whatsappContactRepository;

    protected $whatsappService;

    public function __construct(
        WhatsappContactRepository $whatsappContactRepository,
        WhatsAppService $whatsappService
    ) {
        $this->whatsappContactRepository = $whatsappContactRepository;
        $this->whatsappService = $whatsappService;
    }

    // In your WhatsappController
    public function index()
    {
        $contacts = $this->whatsappContactRepository->getAllContacts();

        return Inertia::render('Whatsapp/Messaging', [
            'contacts' => $contacts,
        ]);
    }

    public function storeContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'required|string|unique:whatsapp_contacts,phone,NULL,id,deleted_at,NULL',
            'profile_pic' => 'nullable|url',
        ]);

        $contact = $this->whatsappContactRepository->create($validated);

        // Check if it was a restoration
        if ($contact->wasChanged('deleted_at') && $contact->deleted_at === null) {
            return redirect()->back()->with('success', 'Previously deleted contact restored successfully.');
        }

        return redirect()->back()->with('success', 'Contact created successfully.');
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

    /**
     * Send a WhatsApp message
     */
    public function sendMessage(SendWhatsappMessageRequest $request): JsonResponse
    {
        try {
            $phone = $this->formatPhoneNumber($request->recipient);
            $message = $request->message;

            // Send message via WhatsApp API
            $response = $this->whatsappService->sendMessage($phone, $message);

            if ($response['success']) {
                // Store the sent message in database
                $messageData = [
                    'phone' => $phone,
                    'message' => $message,
                    'timestamp' => Carbon::now(),
                    'message_id' => $response['message_id'] ?? null,
                    'delivery_status' => 'sent',
                ];

                $storedMessage = $this->whatsappContactRepository->storeSentMessage($messageData);

                return response()->json([
                    'success' => true,
                    'message' => 'Message sent successfully',
                    'data' => [
                        'message_id' => $response['message_id'],
                        'stored_message' => $storedMessage,
                        'timestamp' => $messageData['timestamp']->toISOString(),
                        'recipient' => $phone,
                    ],
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $response['error'] ?? 'Failed to send message',
                ], 400);
            }
        } catch (\Exception $e) {
            Log::error('WhatsApp send message error: '.$e->getMessage(), [
                'recipient' => $request->recipient ?? 'unknown',
                'message_length' => strlen($request->message ?? ''),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while sending the message',
            ], 500);
        }
    }

    /**
     * Get messages for a specific phone number
     */
    public function getMessages($phone)
    {
        try {
            $formattedPhone = $this->formatPhoneNumber($phone);

            $latest = request()->query('latest', false);

            $messages = $this->whatsappContactRepository->getMessagesByPhone($formattedPhone);

            if ($latest && $messages->isNotEmpty()) {
                $messages = $messages->take(1);

            }

            $formattedMessages = $messages->map(function ($message) {
                return [
                    'id' => $message->id,
                    'sender' => $message->is_outgoing ? 'You' : $message->sender_name ?? 'Contact',
                    'avatar' => '/placeholder.svg?height=32&width=32',
                    'content' => $message->message,
                    'time' => Carbon::parse($message->timestamp)->format('H:i'),
                    'outgoing' => $message->is_outgoing,
                    'status' => $message->delivery_status ?? 'delivered',
                    'messageId' => $message->message_id,
                    'timestamp' => $message->timestamp,
                ];
            });

            return response()->json([
                'success' => true,
                'data' => [
                    'messages' => $formattedMessages,
                    'phone' => $formattedPhone,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('WhatsApp get messages error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching messages',
            ], 500);
        }
    }

    /**
     * Format phone number to international format
     */
    private function formatPhoneNumber(string $phone): string
    {
        // Remove any non-digit characters except +
        $phone = preg_replace('/[^0-9+]/', '', $phone);

        // Remove + if present for processing
        $phone = ltrim($phone, '+');

        // Add country code if not present (assuming Sri Lanka +94)
        if (str_starts_with($phone, '0')) {
            // Remove leading 0 and add 94
            $phone = '94'.substr($phone, 1);
        } elseif (! str_starts_with($phone, '94')) {
            // Add 94 if no country code
            $phone = '94'.$phone;
        }

        return $phone;
    }

    public function updateContact(Request $request, $id)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|unique:whatsapp_contacts,phone,'.$id,
                'profile_pic' => 'nullable|url',
            ]);

            $contact = WhatsappContact::findOrFail($id);
            $contact->update($validated);

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error updating WhatsApp contact: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to update contact',
            ], 500);
        }
    }

    public function destroyContact($id)
    {
        try {
            $contact = WhatsappContact::findOrFail($id);
            $contact->delete();

            // Get updated contacts list after deletion
            $updatedContacts = $this->whatsappContactRepository->getAllContacts();

            return response()->json([
                'success' => true,
                'message' => 'Contact deleted successfully',
                'data' => [
                    'contacts' => $updatedContacts,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting WhatsApp contact: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete contact',
            ], 500);
        }
    }
}
