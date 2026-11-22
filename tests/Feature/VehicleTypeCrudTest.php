<?php

namespace Tests\Feature;

use App\Models\DriverProfile;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleTypeCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->actingAs(User::factory()->create(['role' => 'admin']));
    }

    public function test_vehicle_type_pages_render(): void
    {
        $vehicleType = VehicleType::create([
            'name' => 'Van',
            'description' => 'Delivery van',
            'is_active' => true,
        ]);

        $this->get(route('vehicle-types.index'))->assertOk();
        $this->get(route('vehicle-types.create'))->assertOk();
        $this->get(route('vehicle-types.show', $vehicleType))->assertOk();
        $this->get(route('vehicle-types.edit', $vehicleType))->assertOk();
    }

    public function test_vehicle_types_use_distinct_illustrations_when_they_have_no_photo(): void
    {
        $types = [
            'Compact Cargo Van' => 'vehicle-compact-van.svg',
            'Long Wheelbase Van' => 'vehicle-long-van.svg',
            'Demo Enclosed Van' => 'vehicle-enclosed-van.svg',
            'Box Truck' => 'driver-vehicle-truck.svg',
        ];

        foreach ($types as $name => $image) {
            $type = VehicleType::factory()->create(['name' => $name]);
            $this->get(route('vehicle-types.show', $type))
                ->assertOk()
                ->assertSee('landing/img/'.$image);
        }

        $catalog = $this->get(route('vehicle-types.index'))->assertOk();
        foreach ($types as $image) {
            $catalog->assertSee('landing/img/'.$image);
        }
    }

    public function test_vehicle_types_can_be_created_updated_and_deleted(): void
    {
        $this->post(route('vehicle-types.store'), [
            'name' => 'Truck',
            'description' => 'Heavy delivery truck',
            'is_active' => '1',
        ])->assertRedirect(route('vehicle-types.index'));

        $vehicleType = VehicleType::where('name', 'Truck')->firstOrFail();
        $this->assertTrue($vehicleType->is_active);

        $this->put(route('vehicle-types.update', $vehicleType), [
            'name' => 'Small truck',
            'description' => 'Updated description',
            'is_active' => '0',
        ])->assertRedirect(route('vehicle-types.index'));

        $this->assertDatabaseHas('vehicle_types', [
            'id' => $vehicleType->id,
            'name' => 'Small truck',
            'is_active' => false,
        ]);

        $this->delete(route('vehicle-types.destroy', $vehicleType))
            ->assertRedirect(route('vehicle-types.index'));

        $this->assertDatabaseMissing('vehicle_types', ['id' => $vehicleType->id]);
    }

    public function test_vehicle_type_with_vehicles_cannot_be_deleted(): void
    {
        $vehicleType = VehicleType::create([
            'name' => 'Bus',
            'is_active' => true,
        ]);
        $driver = DriverProfile::create([
            'user_id' => User::factory()->create()->id,
            'license_number' => 'LICENSE-1',
            'license_image' => 'license.png',
        ]);
        Vehicle::create([
            'vehicle_type_id' => $vehicleType->id,
            'driver_id' => $driver->id,
            'name' => 'City bus',
            'plate_number' => 'BUS-001',
            'capacity_kg' => 'large',
            'capacity_m3' => 'large',
            'base_price' => 0,
            'price_per_km' => 0,
            'status' => 'active',
        ]);

        $this->delete(route('vehicle-types.destroy', $vehicleType))
            ->assertRedirect(route('vehicle-types.index'))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('vehicle_types', ['id' => $vehicleType->id]);
    }
}
