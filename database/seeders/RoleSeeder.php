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
            'color' => '#0000FF', // Azul para programadores
            'level_permissions' => 99, // Permisos avanzados para programadores
        ]);

        Role::create([
            'name' => 'Barbero',
            'slug' => 'barber',
            'description' => 'Empleado de la barbería',
            'color' => '#00FF00', // Verde para barberos
            'level_permissions' => 10, // Permisos básicos para barberos

        ]);

        Role::create([
            'name' => 'Administrador',
            'slug' => 'manager',
            'description' => 'Administrador de la barbería',
            'color' => '#FF0000', // Rojo para administradores
            'level_permissions' => 99, // Permisos avanzados para administradores

        ]);

        Role::create([
            'name' => 'Usuario',
            'slug' => 'user',
            'description' => 'Cliente registrado en la aplicación',
            'color' => '#0000FF', // Azul para usuarios
            'level_permissions' => 0, // Permisos básicos para usuarios
        ]);
    }
}