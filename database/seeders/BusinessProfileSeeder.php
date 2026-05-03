<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BusinessProfile;

class BusinessProfileSeeder extends Seeder
{
    public function run(): void
    {
        BusinessProfile::create([
            'name' => 'RS Barber Studio',
            'description' => 'Elevamos tu imagen con cortes de precisión y rituales de barbería clásica en el corazón de Manta. Experimenta la diferencia de un acabado maestro.',
            'address' => 'Manta, Manabí, Ecuador',
            'phone' => '593999999999',
            'social_networks' => [
                'instagram' => 'https://instagram.com/rsbarberstudio',
                'facebook' => 'https://facebook.com/rsbarberstudio'
            ],
            'opening_hours' => [
                'Lunes a Jueves' => '8:30 AM - 8:00 PM',
                'Viernes y Sábado' => '8:15 AM - 8:00 PM',
                'Domingo' => '9:30 AM - 2:30 PM'
            ],
            'logo' => null, // Aquí puedes poner una URL o base64
            'extra_info' => [

                // Dirección para Google Maps (puede ser útil para el frontend o para generar un enlace directo a la ubicación)
                'map_query' => 'C. 49 506, Manta 130804, Ecuador',
                
                // Sección de contenido para la página de inicio del frontend (esto es solo un ejemplo, puedes adaptarlo según tus necesidades)
                'hero' => [
                    'subtitle' => 'Donde el Estilo se Encuentra con la Tradición',
                    'bg_image' => 'https://images.unsplash.com/photo-1503951914875-452162b0f3f1?q=80&w=1920'
                ],
                'filosofia' => [
                    'title' => 'MÁS QUE UN CORTE, UNA EXPERIENCIA',
                    'p1' => 'En RS Barber Studio, entendemos que tu cabello y barba son tu carta de presentación. Nuestra filosofía combina la precisión de la barbería de la vieja escuela con las tendencias más vanguardistas.',
                    'p2' => 'Cada cliente es único. Nos tomamos el tiempo para asesorarte y crear un look que no solo te haga ver bien, sino que refleje tu personalidad. Roger M. y su equipo están dedicados a la excelencia en cada detalle.',
                    'image' => 'https://images.unsplash.com/photo-1512690136236-983bee65df4b?q=80&w=600'
                ],
                'destacados' => [
                    [
                        'icon' => '📐',
                        'title' => 'MAESTRÍA Y PRECISIÓN',
                        'description' => 'Nuestros barberos son artesanos. Desde un fade perfecto hasta el diseño de barba más exigente, garantizamos resultados impecables.'
                    ],
                    [
                        'icon' => '🕯️',
                        'title' => 'RITUALES CLÁSICOS',
                        'description' => 'Revive la tradición del afeitado con navaja, toallas calientes y productos premium que cuidan tu piel y te relajan.'
                    ],
                    [
                        'icon' => '☕',
                        'title' => 'AMBIENTE EXCLUSIVO',
                        'description' => 'Un espacio diseñado para tu comodidad. Disfruta de buena música, una bebida y una conversación mientras transformamos tu look.'
                    ]
                ],
                'galeria' => [
                    'https://images.unsplash.com/photo-1621605815971-fbc98d665033?q=80&w=400',
                    'https://images.unsplash.com/photo-1605497788044-5a32c7078486?q=80&w=400',
                    'https://images.unsplash.com/photo-1599351431202-1e0f0137899a?q=80&w=400',
                    'https://images.unsplash.com/photo-1517832606299-7ae9b720a186?q=80&w=400'
                ]
            ]
        ]);
    }
}