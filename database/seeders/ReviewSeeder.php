<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Review;
use App\Models\User;
use App\Models\Service; // <-- No olvides importar el modelo Service

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        // 1. OBTENER IDs DINÁMICAMENTE
        $clientIds = User::whereHas('role', function($q) { $q->where('slug', 'user'); })->pluck('id')->toArray();
        $barberIds = User::whereHas('role', function($q) { $q->where('slug', 'barber'); })->pluck('id')->toArray();
        
        // Obtenemos los IDs de los servicios que realmente existen
        $serviceIds = Service::pluck('id')->toArray();

        // Si la BD está vacía, creamos datos de emergencia para evitar que el seeder explote
        if (empty($clientIds)) $clientIds = range(11, 20); 
        if (empty($barberIds)) $barberIds = range(1, 10);
        
        // Si no hay ningún servicio creado, creamos uno de prueba dinámicamente
        if (empty($serviceIds)) {
            $servicioRespaldo = Service::create([
                'name' => 'Corte Clásico (Auto-generado)',
                'description' => 'Servicio creado automáticamente por el seeder.',
                'price' => 15.00,
                'duration_minutes' => 45,
                'is_active' => true
            ]);
            $serviceIds = [$servicioRespaldo->id];
        }

        // 2. CREAR LA DISTRIBUCIÓN EXACTA DE CALIFICACIONES (40 reseñas)
        $ratings = array_merge(
            array_fill(0, 25, 5),
            array_fill(0, 10, 4),
            array_fill(0, 4, 3),
            [rand(1, 2)] 
        );

        shuffle($ratings);

        // 3. BANCOS DE COMENTARIOS
        $comments5 = [
            '¡Excelente servicio! El barbero fue muy profesional y el corte quedó perfecto.',
            'Me encantó la atención y el ambiente del lugar. 100% recomendado.',
            'Corte impecable, justo lo que pedí. Roger M. y su equipo son unos maestros.',
            'Muy puntuales y el resultado superó mis expectativas. Volveré sin duda.',
            'La mejor barbería de Manta. Atención de primera y muy buen rollo.',
            'Detallistas al máximo. Me fui súper contento con el perfilado de mi barba.',
            'Ritual de toalla caliente increíble, súper relajante. Un 10 de 10.'
        ];

        $comments4 = [
            'Buen corte, aunque tuve que esperar unos 10 minutos. Igual lo recomiendo.',
            'Me gustó bastante. Buena técnica, aunque el local estaba un poco lleno.',
            'Muy buen servicio, el lugar es muy limpio y cómodo.',
            'Satisfecho con el resultado. Todo en orden y buena música.',
            'Buena experiencia en general. El barbero entendió rápido lo que quería.'
        ];

        $comments3 = [
            'El corte estuvo bien, pero nada fuera de lo común.',
            'Aceptable. Podrían mejorar un poco la puntualidad de las citas.',
            'Normal. El barbero estaba un poco apurado, pero el resultado fue ok.',
            'Cumple con lo básico, pero me esperaba un poco más de detalle en el fade.'
        ];

        $comments1_2 = [
            'No quedé contento. Me cortaron más de lo que pedí y no se nota el degradado.',
            'Mala experiencia, tuve que esperar muchísimo y el trato fue un poco frío.',
            'No vuelvo. No escucharon mis indicaciones y me dejaron una marca.'
        ];

        // 4. GENERAR LAS 40 RESEÑAS
        foreach ($ratings as $rating) {
            if ($rating == 5) {
                $comment = $comments5[array_rand($comments5)];
            } elseif ($rating == 4) {
                $comment = $comments4[array_rand($comments4)];
            } elseif ($rating == 3) {
                $comment = $comments3[array_rand($comments3)];
            } else {
                $comment = $comments1_2[array_rand($comments1_2)];
            }

            Review::create([
                'client_id'  => $clientIds[array_rand($clientIds)], 
                'barber_id'  => $barberIds[array_rand($barberIds)], 
                'service_id' => $serviceIds[array_rand($serviceIds)], // <--- Ahora usa IDs reales
                'rating'     => $rating,
                'comment'    => $comment,
            ]);
        }
    }
}