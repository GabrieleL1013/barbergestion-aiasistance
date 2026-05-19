<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\Service;
use App\Models\User;
use App\Models\Role;
use App\Models\Promotion;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $barberRole = Role::where('slug', 'barber')->first();
        $clientRole = Role::where('slug', 'user')->first();

        if (! $barberRole || ! $clientRole) {
            return;
        }

        $barbers = User::where('role_id', $barberRole->id)->get();
        $clients = User::where('role_id', $clientRole->id)->get();
        $services = Service::where('is_active', true)->get();
        $promotion = Promotion::where('is_active', true)
            ->orderBy('required_visits', 'desc')
            ->first();

        if ($barbers->isEmpty() || $clients->isEmpty() || $services->isEmpty()) {
            return;
        }

        $totalAppointments = 50;    // Número total de citas a generar
        $appointmentIndex = 0;      // Índice para controlar la generación de citas
        $baseDate = Carbon::now()->addDays(1)->startOfDay()->addHours(9); // Empezamos a generar citas a partir de mañana a las 9 AM

        DB::transaction(function () use (
            $barbers,
            $clients,
            $services,
            $promotion,
            $totalAppointments,
            &$appointmentIndex,
            $baseDate
        ) {
            foreach ($barbers as $barber) {
                for ($slot = 0; $slot < 4; $slot++) {
                    if ($appointmentIndex >= $totalAppointments) {
                        break 2;
                    }

                    $service = $services->random();
                    $client = $clients->random();

                    $scheduledAt = $baseDate->copy()->addHours($slot)->addDays(intdiv($appointmentIndex, 14));

                    $appointment = Appointment::create([
                        'client_id' => $client->id,
                        'barber_id' => $barber->id,
                        'service_id' => $service->id,
                        'scheduled_at' => $scheduledAt,
                        'status' => 'pending',
                        'notes' => 'Reserva de prueba generada por seeder.',
                    ]);

                    $client->visits_count = $client->visits_count ?: 0;
                    $client->total_lifetime_visits = $client->total_lifetime_visits ?: 0;

                    $discount = 0;
                    $promotionApplied = null;

                    if ($promotion && $promotion->required_visits <= $client->visits_count) {
                        $discount = $service->price * ($promotion->discount_percentage / 100);
                        $promotionApplied = $promotion;
                        $client->visits_count = 0;
                    } else {
                        $client->visits_count += 1;
                    }

                    $client->total_lifetime_visits += 1;
                    $client->save();

                    $sale = Sale::create([
                        'client_id' => $client->id,
                        'barber_id' => $barber->id,
                        'total_amount' => $service->price - $discount,
                        'payment_method' => 'cash',
                    ]);

                    SaleItem::create([
                        'sale_id' => $sale->id,
                        'service_id' => $service->id,
                        'quantity' => 1,
                        'price_at_sale' => $service->price,
                    ]);

                    $appointmentIndex++;
                }
            }
        });
    }
}
