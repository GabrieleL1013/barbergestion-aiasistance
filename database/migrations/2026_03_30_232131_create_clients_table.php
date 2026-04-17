<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            
            // El nombre es lo único estrictamente obligatorio (el caso de no dar uno: sin_nombre)
            $table->string('name'); 
            
            // El resto es opcional (nullable) para agilizar el registro
            $table->string('last_name')->nullable();
            $table->string('ci')->nullable(); // Cédula de identidad, opcional pero útil para clientes frecuentes
            $table->string('phone')->nullable();
            $table->string('email')->nullable();
            
            // Un campo de notas siempre es útil en una barbería (ej: "Alergia a la cera", "Corte degradado alto")
            $table->longText('notes')->nullable(); 
            
            $table->timestamps();
            $table->softDeletes(); // Para permitir eliminar clientes sin perder su historial de pagos o citas
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};