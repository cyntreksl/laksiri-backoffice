<?php

namespace App\Repositories;

use App\Interfaces\WhatsappContactRepositoryInterface;
use App\Models\WhatsappContact;

class WhatsappContactRepository implements WhatsappContactRepositoryInterface
{
    public function create(array $data): WhatsappContact
    {
        return WhatsappContact::create($data);
    }
}
