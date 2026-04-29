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
        $userRole = Role::where('slug', 'user')->first();

        // 1. Gabriele (Sin comisión)
        User::create([
            'name'       => 'Gabriele',
            'last_name'  => 'L.',
            'ci'         => '4315292929',
            'email'      => 'gab@gmail.com',
            'password'   => 'Gab#123', 
            'role_id'    => $adminRole->id,
            'commission' => 0, 
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

        // 4. Crear 10 usuarios comunes
        for ($i = 1; $i <= 10; $i++) {
            $sufijo = str_pad($i, 2, '0', STR_PAD_LEFT); // Genera un sufijo de 2 dígitos (ej: 01, 02, ..., 10)
            User::create([
                'name'       => "Usuario $sufijo",
                'last_name'  => "Prueba $sufijo",
                'ci'         => '12345678' . $sufijo,
                'phone'      => '09876543' . $sufijo,
                'email'      => "usuario{$sufijo}@barberia.com",
                'password'   => 'password123',
                'role_id'    => $userRole->id,
                'commission' => 0,
            ]);
        }

    }
}
