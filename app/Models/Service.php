<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Service extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'duration_minutes',
        'is_active',
        'photo',
        'details',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'details' => 'array',
    ];

    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    public function saleItems() // Para saber en qué ventas se incluyó este servicio
    {
        return $this->hasMany(SaleItem::class);
    }

    public function reviews() // Reseñas de este servicio
    {
        return $this->hasMany(Review::class);
    }
}