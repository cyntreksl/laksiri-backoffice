<?php

namespace Database\Seeders;

use App\Models\CourierAgent;
use Illuminate\Database\Seeder;

class CourierAgentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courierAgents = [
            [
                'company_name' => 'Fast Express',
                'website' => 'https://fastexpress.com',
                'contact_number_1' => '0771234567',
                'contact_number_2' => '0112233445',
                'email' => 'info@fastexpress.com',
                'address' => '123 Main Street, Colombo, Sri Lanka',
                'logo' => null, // You can set a default logo path if needed
                'invoice_header' => 'Fast Express Invoice',
                'invoice_footer' => 'Thank you for choosing Fast Express!',
            ],
            [
                'company_name' => 'Speedy Logistics',
                'website' => 'https://speedylogistics.com',
                'contact_number_1' => '0712345678',
                'contact_number_2' => '0113344556',
                'email' => 'support@speedylogistics.com',
                'address' => '456 Business Road, Galle, Sri Lanka',
                'logo' => null,
                'invoice_header' => 'Speedy Logistics Invoice',
                'invoice_footer' => 'Your trusted delivery partner.',
            ],
            [
                'company_name' => 'Reliable Couriers',
                'website' => 'https://reliablecouriers.com',
                'contact_number_1' => '0756789012',
                'contact_number_2' => '0115566778',
                'email' => 'contact@reliablecouriers.com',
                'address' => '789 Market Avenue, Kandy, Sri Lanka',
                'logo' => null,
                'invoice_header' => 'Reliable Couriers Invoice',
                'invoice_footer' => 'Fast, Secure, and Reliable.',
            ],

        ];
        foreach ($courierAgents as $courierAgent) {
            CourierAgent::create($courierAgent);
        }
    }
}
