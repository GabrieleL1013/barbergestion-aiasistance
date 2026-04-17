<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'cost',
        'price',
        'measure',
        'unit',
        'photo',
    ];

    protected $casts = [
        'price' => 'decimal:2', // Asegura que el precio se maneje con 2 decimales
        'cost' => 'decimal:2',  // Asegura que el costo se maneje con 2 decimales
        'is_active' => 'boolean', // Asegura que is_active se maneje como true/false
    ];

    // Relación: Un Producto puede estar en muchos Pagos (si vendes el mismo shampoo varias veces)
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Relación: Un producto se ha vendido en muchos cobros
    public function amounts()
    {
        return $this->hasMany(Amount::class);
    }
}