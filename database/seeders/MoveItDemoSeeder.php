<?php

namespace Database\Seeders;

use App\Models\InventoryItem;
use App\Models\Payment;
use App\Models\PricingRule;
use App\Models\Review;
use App\Models\Service;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MoveItDemoSeeder extends Seeder
{
    public function run(): void
    {
        if (! app()->environment('local', 'testing')) {
            throw new \RuntimeException('Demo accounts are only available in local and testing environments.');
        }

        $image = file_get_contents(base_path('ai-service/chair.jpg'));
        $receipt = 'receipts/demo-sample.jpg';
        $license = 'licenses/demo-sample.jpg';
        foreach ([$receipt, $license] as $path) {
            if (! Storage::disk('local')->exists($path) && ! Storage::disk('local')->put($path, $image)) {
                throw new \RuntimeException('Could not save the demo attachment.');
            }
        }

        $password = Str::random(24);
        $updatedAccounts = [];
        DB::transaction(function () use ($receipt, $license, $password, &$updatedAccounts) {
            $admin = $this->account('admin', 'Admin', $password, $updatedAccounts);
            $customer = $this->account('customer', 'Customer', $password, $updatedAccounts);
            $driver = $this->account('driver', 'Driver', $password, $updatedAccounts);
            $applicant = $this->account('driver', 'Applicant', $password, $updatedAccounts, false);

            $profile = $driver->driverProfile()->firstOrCreate([], [
                'license_number' => 'DEMO-MOVEIT-001', 'license_image' => $license, 'status' => 'approved',
            ]);
            $applicant->driverProfile()->firstOrCreate([], [
                'license_number' => 'DEMO-MOVEIT-002', 'license_image' => $license, 'status' => 'pending',
            ]);

            $type = VehicleType::firstOrCreate(['name' => 'Demo Enclosed Van'], [
                'description' => 'An enclosed van for a small apartment move.', 'is_active' => true,
            ]);
            $vehicle = Vehicle::firstOrCreate(['plate_number' => 'DEMO-MOVEIT-VAN'], Vehicle::factory()
                ->for($profile, 'driver')->for($type, 'vehicleType')->raw([
                    'plate_number' => 'DEMO-MOVEIT-VAN', 'name' => 'Demo Moving Van', 'capacity_kg' => 'medium', 'capacity_m3' => 'medium',
                    'base_price' => '25.000', 'price_per_km' => '1.250', 'status' => 'active',
                ]));

            $transport = $this->service(Service::TYPE_TRANSPORT, 'Furniture transport', 'fixed', '20.000');
            $workers = $this->service(Service::TYPE_WORKERS, 'Loading workers', 'worker', '10.000');
            $this->service(Service::TYPE_PACKING, 'Packing', 'fixed', '15.000');
            $this->service(Service::TYPE_BOXES, 'Boxes', 'box', '1.500');
            $this->service(Service::TYPE_ASSEMBLY, 'Assembly and installation', 'fixed', '25.000');

            $states = ['draft' => 1, 'pending' => 2, 'awaiting_deposit' => 3, 'confirmed' => 4, 'in_progress' => 0, 'completed' => -7, 'rejected' => -3, 'cancelled' => -2];
            foreach ($states as $status => $days) {
                $start = now()->addDays($days)->setTime(9, 0);
                $booking = $customer->bookings()->firstOrCreate(['notes' => 'MoveIt demo · '.$status], [
                    'status' => $status, 'driver_id' => $profile->id, 'vehicle_id' => $vehicle->id,
                    'pickup_latitude' => 31.9539, 'pickup_longitude' => 35.9106,
                    'destination_latitude' => 31.9701, 'destination_longitude' => 35.9272,
                    'scheduled_start_at' => $start, 'scheduled_end_at' => $start->copy()->addHours(2),
                    'distance_km' => $status === 'draft' ? null : '8.00',
                    'total_price' => $status === 'draft' ? null : '75.000',
                    'deposit_amount' => $status === 'draft' ? null : '20.000',
                    'deposit_due_at' => $status === 'awaiting_deposit' ? now()->addDays(2) : null,
                    'commission_rate' => $status === 'completed' ? '10.00' : null,
                    'commission_amount' => $status === 'completed' ? '7.500' : null,
                    'rejection_reason' => $status === 'rejected' ? 'Demo: driver unavailable.' : null,
                ]);

                $booking->inventoryItems()->firstOrCreate(['item_type' => 'sofa', 'source' => 'manual'], InventoryItem::factory()
                    ->for($booking)->raw(['item_type' => 'sofa', 'quantity' => 2, 'notes' => 'Demo living room']));
                foreach ([$transport, $workers] as $service) {
                    $booking->bookingServices()->firstOrCreate(['service_id' => $service->id], [
                        'service_name' => $service->name, 'unit' => $service->unit, 'quantity' => $service->id === $workers->id ? 2 : 1,
                        'unit_price' => $service->price,
                    ]);
                }

                if (in_array($status, ['awaiting_deposit', 'confirmed', 'in_progress', 'completed'], true)) {
                    $pending = $status === 'awaiting_deposit';
                    $booking->payments()->firstOrCreate(['transfer_reference' => 'DEMO-MOVEIT-'.$status], Payment::factory()
                        ->for($booking)->raw([
                            'transfer_reference' => 'DEMO-MOVEIT-'.$status,
                            'receipt_image' => $receipt, 'status' => $pending ? Payment::STATUS_PENDING : Payment::STATUS_APPROVED,
                            'reviewed_by' => $pending ? null : $admin->id, 'reviewed_at' => $pending ? null : now(),
                        ]));
                }
                if ($status === 'completed') {
                    $booking->review()->firstOrCreate([], ['rating' => 5, 'comment' => 'Demo: furniture arrived in excellent condition.']);
                }
                foreach ([$admin, $customer, $driver] as $recipient) {
                    $recipient->notifications()->firstOrCreate(['booking_id' => $booking->id, 'type' => 'demo'], [
                        'title' => 'Demo booking update', 'message' => 'Example status: '.$status,
                        'url' => route('booking.show', $booking, false),
                    ]);
                }
            }

            PricingRule::firstOrCreate(['name' => 'Demo morning rule'], PricingRule::factory()->raw(['name' => 'Demo morning rule']));
            if (! DB::table('contact_messages')->where('email', 'contact@moveit.test')->where('subject', 'Demo moving inquiry')->exists()) {
                DB::table('contact_messages')->insert([
                    'name' => 'Demo Customer', 'email' => 'contact@moveit.test', 'subject' => 'Demo moving inquiry',
                    'message' => 'Could you help with a small apartment move in Amman?', 'created_at' => now(),
                ]);
            }
        });
        if ($updatedAccounts) {
            $this->command?->info('Demo password for '.implode(', ', $updatedAccounts).': '.$password);
        }
    }

    private function account(string $role, string $label, string $password, array &$updatedAccounts, bool $active = true): User
    {
        $email = strtolower($label).'@moveit.test';
        $user = User::where('email', $email)->first();
        if ($user && ($user->name !== 'Demo '.$label || $user->role !== $role)) {
            throw new \RuntimeException('An existing account already uses demo email '.$email);
        }
        if (!$user) {
            $user = User::factory()->create([
                'email' => $email, 'name' => 'Demo '.$label, 'role' => $role,
                'is_active' => $active, 'password' => $password,
            ]);
            $updatedAccounts[] = $email;
        } elseif (Hash::check('password', $user->password)) {
            $user->update(['password' => $password]);
            $updatedAccounts[] = $email;
        }

        return $user;
    }

    private function service(string $type, string $name, string $unit, string $price): Service
    {
        return Service::firstOrCreate(['name' => $name], [
            'type' => $type, 'unit' => $unit, 'price' => $price, 'is_active' => true,
            'description' => 'MoveIt demo catalog service.',
        ]);
    }
}
