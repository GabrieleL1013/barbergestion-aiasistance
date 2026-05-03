<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BusinessProfile extends Model
{
    use HasFactory;

    protected $table = 'business_profile';

    protected $fillable = [
        'name',
        'description',
        'address',
        'phone',
        'social_networks',
        'opening_hours',
        'logo',
        'extra_info'
    ];

    // Esto es magia pura: convierte los JSON de la BD a arrays en PHP automáticamente
    protected $casts = [
        'social_networks' => 'array',
        'opening_hours' => 'array',
        'extra_info' => 'array',
    ];
}