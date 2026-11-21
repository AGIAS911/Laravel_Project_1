<?php

namespace Tests\Feature;

use App\Models\MoveItNotification;
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

    public function test_guests_can_browse_services_but_cannot_create_them(): void
    {
        $this->get(route('services.index'))
            ->assertOk()
            ->assertSee(route('services.index'));
        $this->get(route('services.create'))
            ->assertRedirect(route('login'));
    }

    public function test_admin_can_complete_service_crud_flow(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $this->get(route('services.index'))
            ->assertOk()
            ->assertSee(route('services.index'))
            ->assertSee('Services')
            ->assertSee('Add service');
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

        $this->get(route('services.index'))
            ->assertOk()
            ->assertSee(route('services.show', $service))
            ->assertSee('Details');

        $this->get(route('services.show', $service))
            ->assertOk()
            ->assertSee('Packing')
            ->assertSee('Delete service');
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

        $this->assertSoftDeleted('services', ['id' => $service->id]);
        $this->assertNull(Service::find($service->id));
        $this->assertNotNull(Service::withTrashed()->find($service->id));
    }

    public function test_only_admins_can_manage_services_and_inactive_services_are_hidden_from_the_public(): void
    {
        $activeService = Service::create([
            'name' => 'Active service',
            'type' => 'moving',
            'unit' => 'fixed',
            'price' => '12.500',
            'is_active' => true,
        ]);
        $inactiveService = Service::create([
            'name' => 'Inactive service',
            'type' => 'moving',
            'unit' => 'fixed',
            'price' => '20.000',
            'is_active' => false,
        ]);

        $this->get(route('services.index'))->assertOk()
            ->assertSee('Active service')->assertDontSee('Inactive service');
        $this->get(route('services.show', $activeService))->assertOk();
        $this->get(route('services.show', $inactiveService))->assertNotFound();

        $this->actingAs(User::factory()->create(['role' => 'customer']));
        $this->get(route('services.index'))->assertRedirect(route('home'));
        $this->get(route('services.show', $activeService))->assertRedirect(route('home'));
        $this->get(route('services.create'))->assertForbidden();
        $this->post(route('services.store'), [])->assertForbidden();
        $this->put(route('services.update', $activeService), [])->assertForbidden();
        $this->delete(route('services.destroy', $activeService))->assertForbidden();
        $this->assertNotSoftDeleted($activeService);
    }

    public function test_customer_navigation_shows_notifications_beside_new_booking_without_services(): void
    {
        $customer = User::factory()->create(['role' => 'customer']);
        MoveItNotification::factory()->forUser($customer)->create(['read_at' => null]);
        MoveItNotification::factory()->forUser($customer)->create(['read_at' => now()]);

        $this->actingAs($customer)->get(route('home'))->assertOk()
            ->assertDontSee(route('services.index'))
            ->assertSeeInOrder(['class="customer-notification-link"', 'New booking'], false)
            ->assertSee('href="'.route('notifications.index').'"', false)
            ->assertSee('aria-label="Notifications, 1 unread"', false);

        $this->get(route('notifications.index'))->assertOk();
        $this->get(route('info.faq'))->assertOk()->assertDontSee(route('services.index'));
    }

    public function test_service_cannot_be_created_with_a_negative_price(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
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
