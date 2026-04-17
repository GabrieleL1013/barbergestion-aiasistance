<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Aquí llamamos a los seeders individuales en el orden correcto
        $this->call([
            RoleSeeder::class, // Primero creamos los roles
            UserSeeder::class, // Luego creamos a Gabriele y a los 14 barberos
            ClientSeeder::class, // Después creamos los 20 clientes
            ProductSeeder::class, // Luego los productos (servicios y productos de venta)
            AmountSeeder::class, // Los ingresos (Amount) que relacionan clientes con barberos y productos, simulando ventas reales
            PaymentSeeder::class, // Finalmente, los pagos (Payment) que relacionan a los barberos con sus comisiones calculadas a partir de los Amounts
        ]);
    }
}