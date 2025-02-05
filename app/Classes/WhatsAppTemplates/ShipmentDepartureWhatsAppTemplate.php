<?php

namespace App\Classes\WhatsAppTemplates;

use App\Classes\WhatsAppTemplate;

class ShipmentDepartureWhatsAppTemplate extends WhatsAppTemplate
{
    public function __construct(string $name, string $hblNumber, string $country)
    {
        parent::__construct('shipment_departure');
        $this->setLanguage('en')
            ->addHeader([
                ['name' => 'country', 'value' => $country],
            ])
            ->addBody([
                ['name' => 'name', 'value' => $name],
                ['name' => 'country', 'value' => $country],
                ['name' => 'hbl_number', 'value' => $hblNumber],
            ])
            ->addUrlButton(0, $hblNumber);

    }
}
