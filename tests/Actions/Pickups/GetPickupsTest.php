<?php

namespace Tests\Actions\Pickups;

use App\Actions\PickUps\GetPickups;
use App\Models\PickUp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GetPickupsTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_pickups_action()
    {

        $pickup1 = PickUp::factory()->create(['name' => 'John Doe']);
        $pickup2 = PickUp::factory()->create(['name' => 'Jane Doe']);

        $getPickupsAction = new GetPickups;

        $pickups = $getPickupsAction->handle();

        $this->assertCount(2, $pickups);
        $this->assertEquals($pickup1->name, $pickups[0]->name);
        $this->assertEquals($pickup2->name, $pickups[1]->name);
    }
}
