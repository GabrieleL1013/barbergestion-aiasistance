<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BusinessProfile extends Model
{
    protected $table = 'business_profile'; // Forzamos el nombre en singular si así está en la migración
    protected $fillable = ['name', 'description', 'address', 'phone', 'social_networks', 'opening_hours', 'logo'];
    protected $casts = [
        'social_networks' => 'array', // Laravel convierte automáticamente el JSON a Array
        'opening_hours' => 'array',
    ];
}