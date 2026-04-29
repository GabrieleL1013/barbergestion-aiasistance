<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Service::create(['name' => 'Corte Clásico', 'price' => 10.00, 'duration_minutes' => 30]);
        Service::create(['name' => 'Barba Express', 'price' => 5.00, 'duration_minutes' => 15]);
        Service::create(['name' => 'Corte + Barba + Cejas', 'price' => 15.00, 'duration_minutes' => 60]);
    }
}
