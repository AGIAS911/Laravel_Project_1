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
            $this->seedCityMoves($admin, $customer, $vehicle, $transport, $workers, $receipt, $license, $password);
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

    private function seedCityMoves(User $admin, User $customer, Vehicle $vehicle, Service $transport, Service $workers, string $receipt, string $license, string $password): void
    {
        $firstNames = ['Lina', 'Omar', 'Dana', 'Laith', 'Nour', 'Yazan', 'Rana', 'Ahmad'];
        $lastNames = ['Haddad', 'Nasser', 'Khalil', 'Saleh', 'Awad', 'Masri'];
        $customers = [$customer];
        for ($i = 0; $i < 48; $i++) {
            $customers[] = User::firstOrCreate(['email' => sprintf('amman.customer.%02d@moveit.test', $i + 1)], [
                'name' => $firstNames[$i % 8].' '.$lastNames[intdiv($i, 8)],
                'password' => $password, 'role' => 'customer', 'is_active' => true, 'email_verified_at' => now(),
            ]);
        }

        $types = [];
        foreach (['Compact Cargo Van' => 'small', 'Long Wheelbase Van' => 'medium', 'Box Truck' => 'large'] as $name => $capacity) {
            $types[] = [VehicleType::firstOrCreate(['name' => $name], ['description' => "Suitable for a $capacity household move.", 'is_active' => true]), $capacity];
        }
        $fleet = [$vehicle];
        for ($i = 0; $i < 15; $i++) {
            $driver = User::firstOrCreate(['email' => sprintf('amman.driver.%02d@moveit.test', $i + 1)], [
                'name' => ['Samer', 'Fadi', 'Tareq', 'Khaled', 'Bilal'][$i % 5].' '.$lastNames[intdiv($i, 5) + 3],
                'password' => $password, 'role' => 'driver', 'is_active' => true, 'email_verified_at' => now(),
            ]);
            $profile = $driver->driverProfile()->firstOrCreate([], [
                'license_number' => 'DEMO-AMMAN-'.($i + 1), 'license_image' => $license, 'status' => 'approved',
            ]);
            [$type, $capacity] = $types[$i % 3];
            $fleet[] = Vehicle::firstOrCreate(['plate_number' => 'DEMO-AMMAN-'.($i + 1)], [
                'driver_id' => $profile->id, 'vehicle_type_id' => $type->id, 'name' => $type->name.' '.($i + 1),
                'capacity_kg' => $capacity, 'capacity_m3' => $capacity,
                'base_price' => 20 + 8 * ($i % 3), 'price_per_km' => 0.85 + 0.20 * ($i % 3), 'status' => 'active',
            ]);
        }

        $areas = [
            ['Jabal Amman', 31.9480, 35.9250], ['Abdoun', 31.9410, 35.8880],
            ['Sweifieh', 31.9532, 35.8649], ['Khalda', 31.9950, 35.8500],
            ['Shmeisani', 31.9764, 35.9006], ['Tabarbour', 32.0110, 35.9450],
            ['Tlaa Al Ali', 31.9969, 35.8699], ['Al Jubeiha', 32.0260, 35.8730],
        ];
        $inventories = [
            ['sofa' => 1, 'tv' => 1, 'bed' => 1, 'chair' => 2],
            ['dining table' => 1, 'chair' => 6, 'refrigerator' => 1, 'oven' => 1],
            ['desk' => 2, 'chair' => 4, 'wardrobe' => 1],
            ['bed' => 2, 'sofa' => 2, 'wardrobe' => 2, 'washing machine' => 1],
        ];
        $today = now(config('booking.timezone'))->startOfDay();
        $counts = ['completed' => 96, 'confirmed' => 42, 'pending' => 24, 'awaiting_deposit' => 32, 'draft' => 32, 'cancelled' => 14];
        foreach ($counts as $state => $count) {
            for ($i = 0; $i < $count; $i++) {
                $owner = $customers[$i % 6 === 0 ? 0 : 1 + (($i + strlen($state) * 7) % 48)];
                [$from, $lat, $lng] = $areas[$i % 8];
                [$to, $destLat, $destLng] = $areas[($i + 1 + intdiv($i, 8) % 7) % 8];
                $note = sprintf('[Amman demo %s-%03d] %s to %s. %s', $state, $i + 1, $from, $to,
                    ['Please call before arrival.', 'Use the service entrance.', 'Loading access from the side street.'][($i + intdiv($i, 8)) % 3]);
                $truck = $fleet[$i % count($fleet)];
                $past = in_array($state, ['completed', 'cancelled'], true);
                $start = $today->copy()->addDays($past ? -2 - $i : 2 + $i)->setTime(8 + 2 * ($i % 5), 0)->utc();
                $assigned = in_array($state, ['confirmed', 'completed'], true);
                $paid = $assigned || $state === 'pending';
                $draft = $state === 'draft';
                $distance = round(hypot(($lat - $destLat) * 111, ($lng - $destLng) * 94) * 1.3, 2);
                $crew = 1 + intdiv($i, 4) % 3;
                $total = round((float) $truck->base_price + $distance * (float) $truck->price_per_km + (float) $transport->price + $crew * (float) $workers->price, 3);
                $booking = $owner->bookings()->firstOrCreate(['notes' => $note], [
                    'status' => $state, 'booking_method' => 'quick', 'preferred_vehicle_type_id' => $truck->vehicle_type_id,
                    'vehicle_id' => $assigned ? $truck->id : null, 'driver_id' => $assigned ? $truck->driver_id : null,
                    'pickup_latitude' => $lat, 'pickup_longitude' => $lng,
                    'destination_latitude' => $destLat, 'destination_longitude' => $destLng,
                    'pickup_floor' => $i % 5, 'destination_floor' => ($i + 2) % 5,
                    'pickup_has_elevator' => $i % 3 !== 0, 'destination_has_elevator' => $i % 4 !== 0,
                    'scheduled_start_at' => $start, 'scheduled_end_at' => $start->copy()->addHours(2),
                    'distance_km' => $draft ? null : $distance, 'total_price' => $draft ? null : $total,
                    'deposit_amount' => $draft ? null : round($total * 0.25, 3),
                    'commission_rate' => $draft ? null : 10, 'commission_amount' => $draft ? null : round($total * 0.1, 3),
                    'deposit_due_at' => $state === 'awaiting_deposit' ? now()->addDay() : null,
                    'deposit_verified_at' => $paid ? ($past ? $start->copy()->subDay() : now()) : null,
                ]);
                if (!$booking->wasRecentlyCreated) {
                    continue;
                }

                foreach ($inventories[$i % 4] as $item => $quantity) {
                    $booking->inventoryItems()->create(['item_type' => $item, 'quantity' => $quantity, 'source' => 'manual', 'status' => 'confirmed']);
                }
                foreach ([$transport, $workers] as $service) {
                    $booking->bookingServices()->create([
                        'service_id' => $service->id, 'service_name' => $service->name,
                        'unit' => $service->unit, 'quantity' => $service->id === $workers->id ? $crew : 1, 'unit_price' => $service->price,
                    ]);
                }
                if ($paid || ($state === 'awaiting_deposit' && $i % 2 === 0)) {
                    $booking->payments()->create([
                        'purpose' => Payment::PURPOSE_DEPOSIT, 'amount' => $booking->deposit_amount,
                        'transfer_reference' => sprintf('DEMO-AMMAN-%s-%03d', $state, $i + 1), 'receipt_image' => $receipt,
                        'status' => $paid ? Payment::STATUS_APPROVED : Payment::STATUS_PENDING,
                        'reviewed_by' => $paid ? $admin->id : null, 'reviewed_at' => $booking->deposit_verified_at,
                    ]);
                }
                if ($state === 'completed' && $i % 4 !== 0) {
                    $booking->review()->create([
                        'rating' => $i % 3 === 0 ? 4 : 5,
                        'comment' => ['Furniture arrived safely.', 'The crew handled the stairs carefully.', 'Pickup was on time.'][$i % 3],
                    ]);
                }
            }
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
