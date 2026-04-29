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

            $table->string('color')->nullable()->default('#000000'); // Color para identificar visualmente el rol en la UI (ej: '#FF0000' para rojo)

            $table->integer('level_permissions')->default(0); // Nivel de permisos para jerarquizar roles (ej: 0 = sin permisos, 1 = básico, 2 = avanzado, etc.)
            
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