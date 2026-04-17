<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Client;

class ClientSeeder extends Seeder
{
    public function run(): void
    {
        // Arreglo con 20 nombres comunes
        $clientNames = [
            'Carlos', 'Andrés', 'Luis', 'María', 'José',
            'Ana', 'Pedro', 'Laura', 'Diego', 'Sofía',
            'Jorge', 'Marta', 'Miguel', 'Lucía', 'David',
            'Carmen', 'Raúl', 'Elena', 'Fernando', 'Patricia'
        ];

        // Generamos la fecha y hora actual forzando la zona horaria de Ecuador continental
        $fechaActualEcuador = now()->setTimezone('America/Guayaquil')->format('d/m/Y H:i');

        foreach ($clientNames as $index => $name) {
            $num = $index + 1;
            
            // Usamos str_pad para mantener un formato de 3 dígitos (001, 002... 020)
            $sufijo = str_pad($num, 3, '0', STR_PAD_LEFT); 

            // Construimos una cédula ficticia de 10 dígitos (ej: 1399999001)
            $cedulaFicticia = '1399999' . $sufijo;

            // Simulamos si el cliente tiene una nota real o no
            $contenidoNota = ($num % 4 == 0) ? "Cliente VIP. Prefiere corte a tijera." : "null";

            // Armamos el string final con el formato obligatorio que pediste y un salto de línea (\n)
            $notaEstandarizada = "[Añadido por sistema: {$fechaActualEcuador}]\nNota: {$contenidoNota}";

            Client::create([
                'name'      => $name,
                'last_name' => 'Cliente ' . $num,
                'ci'        => $cedulaFicticia,
                'phone'     => '0980000' . $sufijo, 
                'email'     => "cliente{$num}@correo.com",
                'notes'     => $notaEstandarizada, // Inyectamos la nota armada aquí
            ]);
        }
    }
}