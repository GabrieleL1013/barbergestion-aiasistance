<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            
            // Nombre visible para los usuarios (ej: 'Administrador', 'Barbero')
            $table->string('name');
            
            // Nombre interno para usar en el código (ej: 'admin', 'barber', manager)
            $table->string('slug')->unique();
            
            // Descipción del Rol. Nullable por si un rol es muy obvio y no requiere descripción
            $table->text('description')->nullable(); 
            
            // Un estado para desactivar roles sin borrarlos de la BD
            $table->boolean('is_active')->default(true); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};