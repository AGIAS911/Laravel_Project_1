<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $services = [
            ['name' => 'Furniture transport', 'type' => Service::TYPE_TRANSPORT, 'unit' => 'fixed', 'price' => 20],
            ['name' => 'Loading workers', 'type' => Service::TYPE_WORKERS, 'unit' => 'worker', 'price' => 10],
            ['name' => 'Packing', 'type' => Service::TYPE_PACKING, 'unit' => 'fixed', 'price' => 15],
            ['name' => 'Boxes', 'type' => Service::TYPE_BOXES, 'unit' => 'box', 'price' => 1.5],
            ['name' => 'Assembly and installation', 'type' => Service::TYPE_ASSEMBLY, 'unit' => 'fixed', 'price' => 25],
        ];

        foreach ($services as $service) {
            Service::updateOrCreate(['type' => $service['type']], $service);
        }
    }
}
