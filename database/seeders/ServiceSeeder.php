<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Service::create(['name' => 'Corte Clásico', 'price' => 10.00, 'duration_minutes' => 30, 'description' => 'Corte de cabello tradicional con máquina o tijera.', 'is_active' => true, 'details' => ['Maquina', 'Tijera', 'Peine'], 'photo' => 'https://www.hola.com/horizon/square/8a5cf9aa438a-gettyimages-2264461813.jpg']);
        Service::create(['name' => 'Barba Express', 'price' => 5.00, 'duration_minutes' => 15, 'description' => 'Raspado y corte de barba.', 'is_active' => true, 'details' => ['Raspado', 'Corte'], 'photo' => 'https://fotografias.antena3.com/clipping/cmsimages02/2022/08/22/FAB19E79-BA9A-4A92-82E8-31F8802F6CB7/hombre-barba_63.jpg']);
        Service::create(['name' => 'Corte + Barba + Cejas', 'price' => 15.00, 'duration_minutes' => 60, 'description' => 'Corte de cabello, raspado y corte de cejas.', 'is_active' => true, 'details' => ['Corte', 'Raspado', 'Corte de Cejas'], 'photo' => 'https://fusionmoda.es/wp-content/uploads/como-tener-ceja-bonita-hombre-1.webp']);
        Service::create(['name' => 'Corte de Niño', 'price' => 8.00, 'duration_minutes' => 25, 'description' => 'Corte de cabello para niños.', 'is_active' => true, 'details' => ['Tijera', 'Peine'], 'photo' => 'https://hips.hearstapps.com/hmg-prod/images/young-boy-having-haircut-royalty-free-image-1676637084.jpg']);
        Service::create(['name' => 'Corte de Cabello + Afeitado', 'price' => 12.00, 'duration_minutes' => 45, 'description' => 'Corte de cabello seguido de un afeitado completo.', 'is_active' => false, 'details' => ['Corte', 'Afeitado'], 'photo' => 'https://4.bp.blogspot.com/-s4n3go-zfGU/Wh06fwiuwpI/AAAAAAAAFBs/YtfCfzs8E0U2fexMbEZyML0ZWI_qs4haQCLcBGAs/s1600/IMG_4533.JPG']);
        Service::create(['name' => 'Corte de Cabello + Barba', 'price' => 12.00, 'duration_minutes' => 45, 'description' => 'Corte de cabello seguido de un raspado y corte de barba.', 'is_active' => false, 'details' => ['Corte', 'Raspado', 'Corte de Barba'], 'photo' => 'https://i.pinimg.com/originals/de/6a/ab/de6aab4c9f8cff89b8f4eac26949cf8c.jpg']);
    }
}
