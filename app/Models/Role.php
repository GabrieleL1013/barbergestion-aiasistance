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
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean', // Asegura que siempre se maneje como true/false
    ];

    // Relación: Un Rol tiene muchos Usuarios
    public function users()
    {
        return $this->hasMany(User::class);
    }
}