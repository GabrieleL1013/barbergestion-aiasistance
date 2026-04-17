<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'admin_id',
        'amount',
        'payment_method',
        'notes',
        'photo',
        'status'
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    // Relación: El empleado que recibe el dinero
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Relación: El dueño que entregó el dinero
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }

    // Relación: Un pago incluye muchos cobros (amounts)
    public function amounts()
    {
        return $this->hasMany(Amount::class);
    }
}