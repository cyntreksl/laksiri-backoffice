<?php

namespace App\Classes\WhatsAppTemplates;

use App\Classes\WhatsAppTemplate;

class CargoCollectedWhatsAppTemplate extends WhatsAppTemplate
{
    public function __construct(string $name, string $hblNumber)
    {
        parent::__construct('cargo_collected');
        $this->setLanguage('en')
            ->addBody([
                ['name' => 'name', 'value' => $name],
                ['name' => 'hbl_number', 'value' => $hblNumber],
            ])
            ->addUrlButton(0, $hblNumber);

    }
}
