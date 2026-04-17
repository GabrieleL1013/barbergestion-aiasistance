<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes; // Notifiable es la magia que conectará con tu tabla notifications

    protected $fillable = [
        'name',
        'last_name',
        'ci',
        'phone',
        'role_id',
        'email',
        'avatar',
        'commission',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // Relación: Un Usuario pertenece a un Rol
    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // Relación: Un Usuario (empleado/dueño) registra muchos Pagos/Transacciones
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    // Las ventas/servicios que este empleado ha realizado
    public function amounts()
    {
        return $this->hasMany(Amount::class);
    }

    // Los pagos/sueldos que este empleado ha recibido
    public function receivedPayments()
    {
        return $this->hasMany(Payment::class, 'user_id');
    }

    // Los pagos que este usuario (si es dueño) ha emitido a otros
    public function issuedPayments()
    {
        return $this->hasMany(Payment::class, 'admin_id');
    }
}