<?php

namespace App\Repositories;

use App\Interfaces\WhatsappContactRepositoryInterface;
use App\Models\WhatsappContact;
use App\Models\WhatsappMessage;

class WhatsappContactRepository implements WhatsappContactRepositoryInterface
{
    public function create(array $data): WhatsappContact
    {
        return WhatsappContact::create($data);
    }

    public function getAllContacts()
    {
        return WhatsappContact::with(['latestMessage'])
            ->withCount(['unreadMessages'])
            ->orderBy('last_interaction', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
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
