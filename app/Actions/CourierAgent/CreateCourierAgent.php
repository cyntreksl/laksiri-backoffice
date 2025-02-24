<?php

namespace App\Actions\CourierAgent;

use App\Models\CourierAgent;
use Lorisleiva\Actions\Concerns\AsAction;

class CreateCourierAgent
{
    use AsAction;

    public function handle(array $data): CourierAgent
    {
        $courierAgent = new CourierAgent;

        $courierAgent->company_name = $data['company_name']  ?? null;
        $courierAgent->website = $data['website']?? null;
        $courierAgent->contact_number_1 = $data['contact_number_1']?? null;
        $courierAgent->contact_number_2 = $data['contact_number_2']?? null;
        $courierAgent->email = $data['email']?? null;
        $courierAgent->address = $data['address']?? null;
        $courierAgent->logo = $data['logo']?? null;
        $courierAgent->invoice_header = $data['invoice_header']?? null;
        $courierAgent->invoice_footer = $data['invoice_footer']?? null;
        $courierAgent->save();

        return $courierAgent;
    }

}
