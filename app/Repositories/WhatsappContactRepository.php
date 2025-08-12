<?php

namespace App\Repositories;

use App\Interfaces\WhatsappContactRepositoryInterface;
use App\Models\WhatsappContact;
use App\Models\WhatsappMessage;
use Carbon\Carbon;

class WhatsappContactRepository implements WhatsappContactRepositoryInterface
{
    public function create(array $data): WhatsappContact
    {
        return WhatsappContact::create($data);
    }

    public function getAllContacts()
    {
        return WhatsappContact::all()->map(function ($contact) {
            return [
                'id' => $contact->id,
                'name' => $contact->name,
                'phone' => $contact->phone,
                'avatar' => $contact->profile_pic ?? '/placeholder.svg?height=48&width=48',
                'lastMessage' => 'No messages yet',
                'time' => now()->format('H:i'),
                'unreadCount' => 0,
                'online' => false,
                'members' => $contact->name,
            ];
        })->toArray();
    }

    public function findByPhone(string $phone): ?WhatsappContact
    {
        return WhatsappContact::where('phone', $phone)->first();
    }

    public function createOrUpdateContact(array $data): WhatsappContact
    {
        $contact = WhatsappContact::updateOrCreate(
            ['phone' => $data['phone']],
            [
                'name' => $data['name'] ?? null,
                'profile_pic' => $data['profile_pic'] ?? null,
                'last_interaction' => $data['last_interaction'] ?? Carbon::now(),
            ]
        );

        return $contact;
    }

    public function storeSentMessage(array $messageData): WhatsappMessage
    {
        // Ensure contact exists and update last_interaction
        $contact = $this->createOrUpdateContact([
            'phone' => $messageData['phone'],
            'name' => $this->findByPhone($messageData['phone'])->name ?? null,
            'last_interaction' => $messageData['timestamp'],
        ]);

        return WhatsappMessage::create([
            'whatsapp_contact_id' => $contact->id,
            'message' => $messageData['message'],
            'message_type' => 'sent',
            'message_id' => $messageData['message_id'] ?? null,
            'sent_at' => $messageData['timestamp'],
            'delivery_status' => $messageData['delivery_status'] ?? 'pending',
            'metadata' => $messageData['metadata'] ?? null,
        ]);
    }

    public function storeReceivedMessage(array $messageData): WhatsappMessage
    {
        // Ensure contact exists and update last_interaction
        $contact = $this->createOrUpdateContact([
            'phone' => $messageData['phone'],
            'name' => $messageData['name'] ?? null,
            'last_interaction' => $messageData['timestamp'],
        ]);

        return WhatsappMessage::create([
            'whatsapp_contact_id' => $contact->id,
            'message' => $messageData['message'],
            'message_type' => 'received',
            'message_id' => $messageData['message_id'] ?? null,
            'received_at' => $messageData['timestamp'],
            'is_read' => false,
            'metadata' => $messageData['metadata'] ?? null,
        ]);
    }

    public function getMessageHistory(string $phone, int $limit = 50)
    {
        $contact = $this->findByPhone($phone);

        if (! $contact) {
            return collect();
        }

        return WhatsappMessage::where('whatsapp_contact_id', $contact->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();
    }

    public function getContactWithMessages(string $phone)
    {
        $contact = $this->findByPhone($phone);

        if (! $contact) {
            return null;
        }

        return $contact->load(['messages' => function ($query) {
            $query->orderBy('created_at', 'asc');
        }]);
    }

    public function getMessagesByPhone(string $phone, int $limit = 50)
    {
        $contact = $this->findByPhone($phone);

        if (! $contact) {
            return collect();
        }

        return WhatsappMessage::where('whatsapp_contact_id', $contact->id)
            ->orderBy('created_at', 'asc')
            ->limit($limit)
            ->get()
            ->map(function ($message) {
                return (object) [
                    'id' => $message->id,
                    'message' => $message->message,
                    'message_id' => $message->message_id,
                    'timestamp' => $message->sent_at ?? $message->received_at ?? $message->created_at,
                    'is_outgoing' => $message->message_type === 'sent',
                    'delivery_status' => $message->delivery_status,
                    'sender_name' => $message->message_type === 'received' ? $contact->name : null,
                ];
            });
    }

    public function markMessagesAsRead(string $phone): void
    {
        $contact = $this->findByPhone($phone);

        if ($contact) {
            WhatsappMessage::where('whatsapp_contact_id', $contact->id)
                ->where('message_type', 'received')
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }
    }

    public function getUnreadMessageCount(string $phone): int
    {
        $contact = $this->findByPhone($phone);

        if (! $contact) {
            return 0;
        }

        return WhatsappMessage::where('whatsapp_contact_id', $contact->id)
            ->where('message_type', 'received')
            ->where('is_read', false)
            ->count();
    }

    public function updateLastInteraction(string $phone): void
    {
        $contact = $this->findByPhone($phone);

        if ($contact) {
            $contact->update(['last_interaction' => Carbon::now()]);
        }
    }
}
