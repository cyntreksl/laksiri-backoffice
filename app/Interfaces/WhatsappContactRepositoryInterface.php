<?php

namespace App\Interfaces;

use App\Models\WhatsappContact;
use App\Models\WhatsappMessage;

interface WhatsappContactRepositoryInterface
{
    public function create(array $data): WhatsappContact;

    public function getAllContacts();

    public function findByPhone(string $phone): ?WhatsappContact;

    public function createOrUpdateContact(array $data): WhatsappContact;

    public function storeSentMessage(array $messageData): WhatsappMessage;

    public function storeReceivedMessage(array $messageData): WhatsappMessage;

    public function getMessageHistory(string $phone, int $limit = 50);

    public function getContactWithMessages(string $phone);

    public function markMessagesAsRead(string $phone): void;

    public function getUnreadMessageCount(string $phone): int;

    public function updateLastInteraction(string $phone): void;
}
