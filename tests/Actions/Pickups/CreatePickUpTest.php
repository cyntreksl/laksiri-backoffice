<?php

namespace Tests\Actions\Pickups;

use App\Actions\PickUps\CreatePickUp;
use App\Models\PickUp;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreatePickUpTest extends TestCase
{
    use RefreshDatabase;

    public function test_create_pick_up_action()
    {
        $this->actingAs(User::factory()->create());

        $data = [
            'cargo_type' => 'sea',
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'contact_number' => '(123) 456-7890',
            'address' => '123 Main Street',
            'location' => 'New York',
            'zone_id' => 1,
            'note' => 'This is a test note.',
        ];

        $createPickUpAction = CreatePickUp::run($data);

        $this->assertInstanceOf(PickUp::class, $createPickUpAction);
        $this->assertDatabaseHas('pick_ups', [
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'contact_number' => '(123) 456-7890',
            'address' => '123 Main Street',
            'location_name' => 'New York',
            'zone_id' => 1,
            'notes' => 'This is a test note.',
            'created_by' => auth()->id(),
        ]);
    }
}
