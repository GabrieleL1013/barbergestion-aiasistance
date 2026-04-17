<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Client extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name',
        'last_name',
        'ci',
        'phone',
        'email',
        'notes',
    ];

    // Relación: Un cliente tiene muchas visitas/servicios (ingresos para la barbería)
    public function amounts()
    {
        return $this->hasMany(Amount::class);
    }
}