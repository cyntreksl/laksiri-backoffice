<?php

namespace App\Classes\WhatsAppTemplates;

use App\Classes\WhatsAppTemplate;

class DriverAssignmentForCargoCollectWhatsAppTemplate extends WhatsAppTemplate
{
    public function __construct(string $name, string $driverName, string $driverContact)
    {
        parent::__construct('driver_assignment_for_cargo_collection');
        $this->setLanguage('en')
            ->addBody([
                ['name' => 'name', 'value' => $name],
                ['name' => 'driver_name', 'value' => $driverName],
                ['name' => 'driver_contact', 'value' => $driverContact],
            ]);
    }
}
