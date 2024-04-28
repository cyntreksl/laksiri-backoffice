<?php

namespace Tests\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Tests\TestCase;

class PickupControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;

    public function test_pickups_index_return_a_successful_response()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->get('/pickups');
        $response->assertStatus(200);
    }

    public function test_pickups_create_return_a_successful_response()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $response = $this->get('/pickups/create');
        $response->assertStatus(200);
    }

    public function test_store_method_successfully_stores_pickup()
    {
        $user = User::factory()->create();
        $this->actingAs($user);
        $this->withoutExceptionHandling();

        $data = [
            'cargo_type' => 'Sea Cargo',
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'contact_number' => '(123) 456-7890',
            'address' => '123 Main Street',
            'location_name' => 'New York',
            'zone_id' => 1,
            'notes' => 'This is a test note.',
        ];

        $response = $this->post(route('pickups.store'),  [
            'cargo_type' => 'sea',
            'name' => 'John Doe',
            'email' => 'john.doe@example.com',
            'contact_number' => '(123) 456-7890',
            'address' => '123 Main Street',
            'location' => 'New York',
            'zone_id' => 1,
            'note' => 'This is a test note.',
        ]);


        $response->assertStatus(200); // 302 Found (redirect)
        $this->assertDatabaseHas('pick_ups', $data);
    }

}
