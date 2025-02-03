<?php

namespace Tests\Repositories;

use App\Actions\PickUps\CreatePickUp;
use App\Actions\PickUps\GetPickups;
use App\Models\PickUp;
use App\Models\User;
use App\Repositories\PickupRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class PickupRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_pickups_method_in_repository()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $pickup1 = PickUp::factory()->create(['name' => 'John Doe']);
        $pickup2 = PickUp::factory()->create(['name' => 'Jane Doe']);

        $getPickupsActionMock = Mockery::mock(GetPickups::class);
        $getPickupsActionMock
            ->expects('handle')
            ->andReturns([$pickup1, $pickup2]);
        $this->app->instance(GetPickups::class, $getPickupsActionMock);
        $pickupRepository = new PickupRepository;
        $pickups = $pickupRepository->getPickups();

        $this->assertCount(2, $pickups);
        $this->assertEquals($pickup1->name, $pickups[0]->name);
        $this->assertEquals($pickup2->name, $pickups[1]->name);
    }

    public function test_store_pickup_method_in_repository()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $data = [
            'cargo_type' => 'sea',
            'name' => 'John Z',
            'email' => 'test@example.com',
            'contact_number' => '(123) 456-7890',
            'address' => '123 Main Street',
            'location_name' => 'New York',
            'zone_id' => 1,
            'notes' => 'This is a test note.',
        ];

        $pickup = PickUp::factory()->create($data);

        $createPickUpMock = Mockery::mock(CreatePickUp::class);
        $createPickUpMock
            ->expects('handle')
            ->with($data)
            ->andReturns($pickup);

        $this->app->instance(CreatePickUp::class, $createPickUpMock);

        $pickupRepository = new PickupRepository;
        $storedPickup = $pickupRepository->storePickup($data);

        $this->assertInstanceOf(PickUp::class, $storedPickup);
        $this->assertDatabaseHas('pick_ups', [
            'name' => 'John Z',
            'email' => 'test@example.com',
            'contact_number' => '(123) 456-7890',
            'address' => '123 Main Street',
            'location_name' => 'New York',
            'zone_id' => 1,
            'notes' => 'This is a test note.',
            'created_by' => 1,
        ]);
    }
}
