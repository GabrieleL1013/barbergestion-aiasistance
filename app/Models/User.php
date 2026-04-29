<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens, SoftDeletes;

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
        'visits_count', // Nuevo: Para la fidelidad
        'total_lifetime_visits' // Nuevo: Para estadísticas
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

    public function role()
    {
        return $this->belongsTo(Role::class);
    }

    // --- RELACIONES COMO CLIENTE ---
    public function appointmentsAsClient()
    {
        return $this->hasMany(Appointment::class, 'client_id');
    }

    public function purchases() // Compras realizadas por este cliente
    {
        return $this->hasMany(Sale::class, 'client_id');
    }

    // --- RELACIONES COMO BARBERO ---
    public function appointmentsAsBarber()
    {
        return $this->hasMany(Appointment::class, 'barber_id');
    }

    public function salesMade() // Ventas/Cobros registrados por este barbero
    {
        return $this->hasMany(Sale::class, 'barber_id');
    }
}