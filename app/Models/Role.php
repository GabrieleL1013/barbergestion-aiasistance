<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'color',
        'level_permissions',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean', // Asegura que siempre se maneje como true/false
        'level_permissions' => 'integer', // Asegura que siempre se maneje como entero
        'color' => 'string', // Asegura que siempre se maneje como cadena de texto

    ];

    // Relación: Un Rol tiene muchos Usuarios
    public function users()
    {
        return $this->hasMany(User::class);
    }
}