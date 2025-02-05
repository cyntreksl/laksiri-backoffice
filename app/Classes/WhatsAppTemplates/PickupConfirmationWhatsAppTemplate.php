<?php

namespace App\Classes\WhatsAppTemplates;

use App\Classes\WhatsAppTemplate;

class PickupConfirmationWhatsAppTemplate extends WhatsAppTemplate
{
    public function __construct(string $name, string $pickUpRef)
    {
        parent::__construct('pickup_confirmation');
        $this->setLanguage('en')
            ->addBody([
                ['name' => 'name', 'value' => $name],
                ['name' => 'pickup_ref', 'value' => $pickUpRef],
            ]);
    }
}
