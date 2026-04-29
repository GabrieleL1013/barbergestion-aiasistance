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
            BusinessProfileSeeder::class, // Luego el perfil del negocio
            ProductSeeder::class, // Luego los productos (servicios y productos de venta)
            ServiceSeeder::class, // Luego los servicios
            PromotionSeeder::class, // Luego las promociones
        ]);
    }
}