<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Amount;
use App\Models\User;
use App\Models\Client;
use App\Models\Product;
use App\Models\Role;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Notification; // <-- IMPORTACIÓN PARA ENVIAR A VARIOS
use App\Notifications\NewAmountNotification; // <-- IMPORTACIÓN DE TU NOTIFICACIÓN

class AmountSeeder extends Seeder
{
    public function run(): void
    {
        $barberRole = Role::where('slug', 'barber')->first();
        $barberIds = User::where('role_id', $barberRole->id)->pluck('id')->toArray();
        $clientIds = Client::pluck('id')->toArray();
        
        // Traemos todos los productos completos para poder leer su precio
        $productos = Product::all();

        // TRUCO DE RENDIMIENTO: 
        // Buscamos a los jefes (admin/manager) UNA SOLA VEZ antes del ciclo para no saturar la BD
        $adminsAndManagers = User::whereHas('role', function($q) {
            $q->whereIn('slug', ['admin', 'manager']);
        })->get();

        $paymentMethods = ['Efectivo', 'Transferencia', 'Tarjeta', 'De Una'];

        for ($i = 1; $i <= 150; $i++) {
            
            // Siempre seleccionamos un producto/servicio del catálogo
            $productoSeleccionado = $productos->random();

            $isRegisteredClient = (rand(1, 100) <= 80);
            $selectedClient = $isRegisteredClient ? Arr::random($clientIds) : null;

            // --- LA MAGIA DEL TIEMPO ---
            // Le damos un 20% de probabilidad de que el corte se haya hecho HOY.
            // Si no, lo mandamos al pasado (entre 1 y 30 días atrás).
            $esDeHoy = (rand(1, 100) <= 20);
            $fechaCreacion = $esDeHoy ? now() : now()->subDays(rand(1, 30));

            // Guardamos el cobro creado en una variable ($amount)
            $amount = Amount::create([
                'user_id'        => Arr::random($barberIds),
                'client_id'      => $selectedClient,
                'product_id'     => $productoSeleccionado->id, 
                'amount'         => $productoSeleccionado->price, 
                'payment_method' => Arr::random($paymentMethods),
                'notes'          => null, 
                'created_at'     => $fechaCreacion, // Usamos la fecha calculada
            ]);

            // Disparamos la notificación a los jefes usando el cobro que acabamos de crear
            Notification::send($adminsAndManagers, new NewAmountNotification($amount));
        }
    }
}