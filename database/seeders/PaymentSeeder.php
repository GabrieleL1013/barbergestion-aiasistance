<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Payment;
use App\Models\Amount;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Arr;
use App\Notifications\NewPaymentNotification; // <-- 1. IMPORTACIÓN AÑADIDA

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('email', 'gabrielelucaszambrano2003@gmail.com')->first();
        
        $barberRole = Role::where('slug', 'barber')->first();
        $barbers = User::where('role_id', $barberRole->id)->get();

        for ($i = 1; $i <= 25; $i++) {
            
            $barber = $barbers->random();

            // OJO: Traemos los cobros CON sus productos para ver el "cost"
            $cobrosPendientes = Amount::with('product')
                                      ->where('user_id', $barber->id)
                                      ->whereNull('payment_id')
                                      ->inRandomOrder()
                                      ->take(rand(3, 8))
                                      ->get();

            if ($cobrosPendientes->isEmpty()) {
                continue; 
            }

            // 3. NUEVA MATEMÁTICA: Calculando la ganancia real
            $pagoCalculado = 0;
            $porcentaje = $barber->commission / 100;

            foreach ($cobrosPendientes as $cobro) {
                if ($cobro->product_id && $cobro->product) {
                    // Si hay producto, descontamos el costo a la barbería
                    $gananciaReal = $cobro->amount - $cobro->product->cost;
                    if ($gananciaReal < 0) { $gananciaReal = 0; } 
                    
                    $pagoCalculado += ($gananciaReal * $porcentaje);
                } else {
                    // Si es puro servicio, va todo
                    $pagoCalculado += ($cobro->amount * $porcentaje);
                }
            }

            $estado = ($i <= 10) ? 'accepted' : 'pending';
            $metodoPago = ($estado === 'accepted') ? Arr::random(['Efectivo', 'Transferencia']) : null;

            $payment = Payment::create([
                'user_id'        => $barber->id,
                'admin_id'       => $admin->id,
                'amount'         => $pagoCalculado,
                'payment_method' => $metodoPago,
                'status'         => $estado,
                'notes'          => "Pago automático por {$cobrosPendientes->count()} servicios. Calculado sobre la ganancia real.",
                'created_at'     => now()->subDays(rand(0, 15)), 
            ]);

            Amount::whereIn('id', $cobrosPendientes->pluck('id'))->update(['payment_id' => $payment->id]);

            // --- 2. LÓGICA DE NOTIFICACIONES AÑADIDA ---
            // Le pedimos al seeder que también genere la notificación en la base de datos
            $barber->notify(new NewPaymentNotification($payment));
        }
    }
}