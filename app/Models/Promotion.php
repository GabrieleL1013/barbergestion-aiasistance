<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $fillable = ['name', 'required_visits', 'discount_percentage', 'is_active'];
    protected $casts = ['is_active' => 'boolean', 'discount_percentage' => 'decimal:2'];
}