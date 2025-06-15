<?php

namespace App\Console\Commands;

use App\Enum\CargoType;
use App\Enum\HBLType;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;

class GenerateFakeHBLData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hbl:generate-fake {count=10 : Number of fake HBL records to generate} {--user= : User ID to use for creating HBLs} {--debug : Show payload structure without making API calls}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate fake HBL data using API endpoints';

    private $apiToken = 'driXAHHO5DSmcChwjg0fhQpPb6cuS1zOGn1TRnRyb421d730';
    private $baseUrl;

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $count = (int) $this->argument('count');
        $userId = $this->option('user');
        $debug = $this->option('debug');

        if ($count <= 0) {
            $this->error('Count must be a positive integer');
            return 1;
        }

        // Get user by ID if provided, otherwise use first user
        if ($userId) {
            $user = User::find($userId);
            if (!$user) {
                $this->error("User with ID {$userId} not found.");
                return 1;
            }
        } else {
            $user = User::first();
            if (!$user) {
                $this->error('No users found. Please create a user first.');
                return 1;
            }
        }

        $faker = Faker::create();

        // Set base URL for API calls
        $this->baseUrl = config('app.api_url', 'http://localhost:8000');

        if ($debug) {
            $this->info("Debug mode: Showing payload structure for API calls...");
        } else {
            $this->info("Generating {$count} fake HBL records via API...");
        }

        $progressBar = $this->output->createProgressBar($count);
        $progressBar->start();

        $createdHBLs = [];
        $packageTypes = ['Box', 'Crate', 'Bag', 'Barrel', 'Pallet', 'Envelope'];

        for ($i = 0; $i < $count; $i++) {
            try {
                // Generate random warehouse and cargo/hbl types
                $warehouse = $faker->randomElement(['COLOMBO']);
                $cargoType = $faker->randomElement([CargoType::SEA_CARGO->value, CargoType::AIR_CARGO->value]);
                $hblType = $faker->randomElement([HBLType::UPB->value, HBLType::GIFT->value]);

                // Generate packages data
                $packagesCount = $faker->numberBetween(1, 5);
                $packages = [];
                $grandVolume = 0;
                $grandWeight = 0;

                for ($j = 0; $j < $packagesCount; $j++) {
                    $length = $faker->numberBetween(20, 100);
                    $width = $faker->numberBetween(20, 100);
                    $height = $faker->numberBetween(10, 80);
                    $quantity = $faker->numberBetween(1, 5);
                    $volume = $length * $width * $height * $quantity;
                    $weight = $faker->randomFloat(2, 5, 50);

                    $packages[] = [
                        'package_type' => $faker->randomElement($packageTypes),
                        'length' => $length,
                        'width' => $width,
                        'height' => $height,
                        'quantity' => $quantity,
                        'volume' => $volume,
                        'weight' => $weight,
                        'remarks' => $faker->optional()->words(3, true),
                        'measure_type' => 'cm',
                        'rule_id' => null
                    ];

                    $grandVolume += $volume;
                    $grandWeight += $weight;
                }

                // First, call calculate payment API
                $paymentData = [
                    'cargo_type' => $cargoType,
                    'hbl_type' => $hblType,
                    'grand_total_volume' => $grandVolume,
                    'grand_total_weight' => $grandWeight,
                    'package_list_length' => count($packages),
                    'is_active_package' => 0,
                    'warehouse' => $warehouse,
                    'package_list' => array_map(function ($pkg) {
                        return [
                            'package_type' => $pkg['package_type'],
                            'custom_type' => '',
                            'length' => $pkg['length'],
                            'width' => $pkg['width'],
                            'height' => $pkg['height'],
                            'quantity' => (string) $pkg['quantity'],
                            'volume' => (string) $pkg['volume'],
                            'weight' => (string) $pkg['weight'],
                            'remarks' => $pkg['remarks'] ?? ''
                        ];
                    }, $packages)
                ];

                if ($debug) {
                    $this->newLine();
                    $this->info("=== Payment Calculation API Payload ===");
                    $this->line(json_encode($paymentData, JSON_PRETTY_PRINT));
                    $this->newLine();
                }

                if (!$debug) {
                    $paymentResponse = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $this->apiToken,
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json'
                    ])->post($this->baseUrl . '/v1/hbls/calculate/payment', $paymentData);

                    if (!$paymentResponse->successful()) {
                        $this->error("\nError calculating payment for HBL #" . ($i + 1) . ": " . $paymentResponse->body());
                        continue;
                    }

                    $paymentResult = $paymentResponse->json();

                    // Extract payment details from response
                    $freightCharge = (float) ($paymentResult['freight_charge'] ?? 0);
                    $billCharge = (float) ($paymentResult['bill_charge'] ?? 0);
                    $otherCharge = (float) ($paymentResult['other_charge'] ?? 0);
                    $packageCharges = (float) ($paymentResult['package_charges'] ?? 0);
                    $destinationCharges = (float) ($paymentResult['destination_charges'] ?? 0);
                    $grandTotalWithoutDiscount = (float) ($paymentResult['grand_total_without_discount'] ??
                        ($freightCharge + $billCharge + $otherCharge + $packageCharges + $destinationCharges));
                } else {
                    // Mock payment response for debug mode
                    $freightCharge = 2000;
                    $billCharge = 500;
                    $otherCharge = 300;
                    $packageCharges = 100;
                    $destinationCharges = 1500;
                    $grandTotalWithoutDiscount = 4400;
                }

                $discount = $faker->randomFloat(2, 0, $grandTotalWithoutDiscount * 0.1);
                $additionalCharge = $faker->randomFloat(2, 0, 200);
                $grandTotal = $grandTotalWithoutDiscount + $additionalCharge - $discount;
                $paidAmount = $faker->randomFloat(2, 0, $grandTotal);

                // Generate shipper data
                $shipperName = $faker->name;
                $shipperEmail = $faker->safeEmail;

                // Generate phone numbers in international format for phone:INTERNATIONAL validation
                $contactNumber = '+94 ' . $faker->numerify('77 ### ####');
                $shipperContact = '+94 ' . $faker->numerify('77 ### ####');
                $consigneeContact = '+94 ' . $faker->numerify('77 ### ####');

                // Prepare HBL creation data to match exact API structure
                $hblData = [
                    'cargo_type' => $cargoType,
                    'hbl_type' => $hblType,
                    'warehouse' => $warehouse,

                    'hbl_name' => $faker->name,
                    'email' => $faker->unique()->safeEmail,
                    'contact_number' => $contactNumber,
                    'additional_mobile_number' => '+94 ' . $faker->numerify('77 ### ####'),
                    'whatsapp_number' => $contactNumber, // Use same as contact_number
                    'nic' => $faker->numerify('##########V'),
                    'iq_number' => $faker->optional()->numerify('IQ######'),
                    'address' => $faker->address,

                    'shipper_name' => $shipperName,
                    'shipper_email' => $shipperEmail,
                    'shipper_address' => $faker->address,
                    'shipper_contact' => $shipperContact,
                    'shipper_id' => $faker->numberBetween(100, 999),

                    'consignee_name' => $faker->name,
                    'consignee_nic' => $faker->numerify('##########V'),
                    'consignee_contact' => $consigneeContact,
                    'consignee_additional_mobile_number' => '+94 ' . $faker->numerify('77 ### ####'),
                    'consignee_whatsapp_number' => $consigneeContact, // Use same as consignee_contact
                    'consignee_address' => $faker->address,
                    'consignee_note' => $faker->optional()->sentence,

                    'packages' => $packages,

                    'freight_charge' => $freightCharge,
                    'bill_charge' => $billCharge,
                    'other_charge' => $otherCharge,
                    'destination_charge' => $destinationCharges,
                    'additional_charge' => $additionalCharge,
                    'discount' => $discount,
                    'paid_amount' => $paidAmount,
                    'grand_total' => $grandTotal,
                    'grand_total_without_discount' => $grandTotalWithoutDiscount,

                    'is_departure_charges_paid' => $faker->boolean ? 1 : 0,
                    'is_destination_charges_paid' => $faker->boolean ? 1 : 0,

                    'grand_volume' => $grandVolume,
                    'grand_weight' => $grandWeight
                ];

                if ($debug) {
                    $this->info("=== HBL Creation API Payload ===");
                    $this->line(json_encode($hblData, JSON_PRETTY_PRINT));
                    $this->newLine();

                    // Mock successful creation for debug mode
                    $createdHBLs[] = [
                        'hbl_id' => $faker->numberBetween(1, 1000),
                        'reference' => 'QT-REF' . str_pad($faker->numberBetween(1, 999999), 6, '0', STR_PAD_LEFT),
                        'hbl_name' => $hblData['hbl_name'],
                        'cargo_type' => $hblData['cargo_type'],
                        'hbl_type' => $hblData['hbl_type'],
                        'warehouse' => $hblData['warehouse'],
                        'grand_total' => $hblData['grand_total']
                    ];
                } else {
                    // Create HBL via API
                    $hblResponse = Http::withHeaders([
                        'Authorization' => 'Bearer ' . $this->apiToken,
                        'Accept' => 'application/json',
                        'Content-Type' => 'application/json'
                    ])->post($this->baseUrl . '/v1/hbls', $hblData);

                    if (!$hblResponse->successful()) {
                        $this->error("\nError creating HBL #" . ($i + 1) . ": " . $hblResponse->body());
                        continue;
                    }

                    $hblResult = $hblResponse->json();
                    $createdHBLs[] = $hblResult;
                }

                $progressBar->advance();
            } catch (\Exception $e) {
                $this->error("\nError creating HBL #" . ($i + 1) . ": " . $e->getMessage());
                continue;
            }
        }

        $progressBar->finish();

        $this->newLine(2);
        if ($debug) {
            $this->info("Debug mode completed! Showed payload structure for " . count($createdHBLs) . " fake HBL records.");
        } else {
            $this->info("Successfully created " . count($createdHBLs) . " fake HBL records via API!");
        }

        if (!empty($createdHBLs)) {
            $tableData = collect($createdHBLs)->take(5)->map(function ($hbl) {
                return [
                    $hbl['hbl_id'] ?? $hbl['id'] ?? 'N/A',
                    $hbl['reference'] ?? 'N/A',
                    $hbl['hbl_name'] ?? 'N/A',
                    $hbl['cargo_type'] ?? 'N/A',
                    $hbl['hbl_type'] ?? 'N/A',
                    $hbl['warehouse'] ?? 'N/A',
                    isset($hbl['grand_total']) ? number_format($hbl['grand_total'], 2) : 'N/A',
                ];
            })->toArray();

            $this->table(
                ['ID', 'Reference', 'HBL Name', 'Cargo Type', 'HBL Type', 'Warehouse', 'Grand Total'],
                $tableData
            );

            if (count($createdHBLs) > 5) {
                $this->info("... and " . (count($createdHBLs) - 5) . " more records.");
            }
        }

        return 0;
    }
}
