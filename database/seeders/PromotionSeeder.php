<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PromotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\Promotion::create([
            'name' => 'Fidelidad: 50% en tu 3ra Visita',
            'required_visits' => 3,
            'discount_percentage' => 50.00,
            'is_active' => true,
        ]);
    }
}
