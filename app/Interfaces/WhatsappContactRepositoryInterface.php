<?php

namespace App\Interfaces;

use App\Models\WhatsappContact;

interface WhatsappContactRepositoryInterface
{
    public function create(array $data): WhatsappContact;
}
