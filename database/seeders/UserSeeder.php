<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Arr;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::where('slug', 'admin')->first();
        $barberRole = Role::where('slug', 'barber')->first();

        // 1. Gabriele (Sin comisión)
        User::create([
            'name'       => 'Gabriele',
            'last_name'  => 'L.',
            'ci'         => '1312292929',
            'email'      => 'gabrielelucaszambrano2003@gmail.com',
            'password'   => 'gab123', 
            'role_id'    => $adminRole->id,
            'commission' => 0, // Tú no cobras comisión
        ]);

        $barberNames = [
            'Mateo', 'Santiago', 'Matías', 'Sebastián', 'Alejandro',
            'Nicolás', 'Samuel', 'Daniel', 'Martín', 'Tomás',
            'Emiliano', 'Joaquín', 'Agustín', 'Lucas'
        ];

        // 3. Crear los 14 barberos
        foreach ($barberNames as $index => $name) {
            $num = $index + 1; 
            $sufijo = str_pad($num, 2, '0', STR_PAD_LEFT); 
            $cedulaFicticia = '13555555' . $sufijo;

            User::create([
                'name'       => $name,
                'last_name'  => 'Prueba ' . $num,
                'ci'         => $cedulaFicticia,
                'phone'      => '09999999' . $sufijo,
                'email'      => "barbero{$num}@barberia.com",
                'password'   => 'password123', 
                'role_id'    => $barberRole->id,
                // Asignamos comisiones realistas al azar (ej: 40%, 50% o 60%)
                'commission' => Arr::random([40, 50, 60]), 
            ]);
        }
    }
}