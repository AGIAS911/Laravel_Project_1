<?php

namespace Tests\Feature;

use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServiceFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    public function test_guests_are_redirected_from_services(): void
    {
        $this->get(route('services.index'))
            ->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_complete_service_crud_flow(): void
    {
        $this->actingAs(User::factory()->create());

        $this->get(route('services.index'))->assertOk();
        $this->get(route('services.create'))->assertOk();

        $this->post(route('services.store'), [
            'name' => 'Packing',
            'description' => 'Packing service',
            'type' => 'moving',
            'unit' => 'per_box',
            'price' => '12.500',
            'is_active' => '1',
        ])->assertRedirect(route('services.index'))
            ->assertSessionHas('success');

        $service = Service::firstOrFail();
        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Packing',
            'is_active' => true,
        ]);

        $this->get(route('services.show', $service))
            ->assertOk()
            ->assertSee('Packing');
        $this->get(route('services.edit', $service))->assertOk();

        $this->put(route('services.update', $service), [
            'name' => 'Packing and Unpacking',
            'description' => 'Updated service',
            'type' => 'moving',
            'unit' => 'per_box',
            'price' => '15.750',
        ])->assertRedirect(route('services.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'name' => 'Packing and Unpacking',
            'price' => '15.750',
            'is_active' => false,
        ]);

        $this->delete(route('services.destroy', $service))
            ->assertRedirect(route('services.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('services', ['id' => $service->id]);
    }

    public function test_service_cannot_be_created_with_a_negative_price(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('services.store'), [
                'name' => 'Packing',
                'type' => 'moving',
                'unit' => 'per_box',
                'price' => '-1',
            ])
            ->assertSessionHasErrors('price');

        $this->assertDatabaseCount('services', 0);
    }
}
