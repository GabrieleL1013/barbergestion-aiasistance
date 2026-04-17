<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            // SERVICIOS DE BARBERÍA (Costo 0, porque es mano de obra)
            ['name' => 'Corte Clásico', 'desc' => 'Corte a tijera o máquina tradicional', 'cost' => 0.00, 'price' => 5.00, 'm' => '1', 'u' => 'servicio'],
            ['name' => 'Corte + Barba', 'desc' => 'Servicio completo con perfilado', 'cost' => 0.00, 'price' => 8.00, 'm' => '1', 'u' => 'servicio'],
            ['name' => 'Solo Barba', 'desc' => 'Alineación y rebaje de barba', 'cost' => 0.00, 'price' => 4.00, 'm' => '1', 'u' => 'servicio'],
            ['name' => 'Corte Degradado (Fade)', 'desc' => 'Degradado con navaja o shaver', 'cost' => 0.00, 'price' => 6.00, 'm' => '1', 'u' => 'servicio'],
            ['name' => 'Corte de Niño', 'desc' => 'Corte para menores de 12 años', 'cost' => 0.00, 'price' => 4.00, 'm' => '1', 'u' => 'servicio'],
            ['name' => 'Tinte de Cabello/Barba', 'desc' => 'Aplicación de color', 'cost' => 2.00, 'price' => 15.00, 'm' => '1', 'u' => 'servicio'], // Aquí sí hay un pequeño costo por el químico usado

            // CUIDADO DEL CABELLO Y BARBA
            ['name' => 'Cera Mate Pomade', 'desc' => 'Fijación fuerte, acabado sin brillo', 'cost' => 8.00, 'price' => 15.00, 'm' => '100', 'u' => 'gr'],
            ['name' => 'Gel Extrafuerte', 'desc' => 'Efecto húmedo de larga duración', 'cost' => 4.00, 'price' => 8.50, 'm' => '250', 'u' => 'ml'],
            // ... (Puedes dejar el resto de tus productos físicos intactos aquí debajo)
            ['name' => 'Agua Mineral', 'desc' => 'Sin gas, bien helada', 'cost' => 0.40, 'price' => 1.00, 'm' => '500', 'u' => 'ml'],
            ['name' => 'Coca-Cola Clásica', 'desc' => 'Bebida gaseosa', 'cost' => 0.60, 'price' => 1.50, 'm' => '350', 'u' => 'ml'],
        ];

        foreach ($products as $p) {
            Product::create([
                'name'        => $p['name'],
                'description' => $p['desc'],
                'cost'        => $p['cost'],
                'price'       => $p['price'],
                'measure'     => $p['m'],
                'unit'        => $p['u'],
                'photo'       => null, 
            ]);
        }
    }
}