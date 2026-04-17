<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        Role::create([
            'name' => 'Programador',
            'slug' => 'admin',
            'description' => 'Programador de la aplicación',
        ]);

        Role::create([
            'name' => 'Barbero',
            'slug' => 'barber',
            'description' => 'Empleado de la barbería',
        ]);

        Role::create([
            'name' => 'Administrador',
            'slug' => 'manager',
            'description' => 'Administrador de la barbería',
        ]);
    }
}