<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nombre
            $table->string('last_name')->nullable(); // Apellido
            $table->string('ci')->nullable()->unique()->max(10)->min(10); // Cédula de identidad, única y con validación de longitud (exactamente 10 caracteres)
            $table->string('phone')->nullable(); // Teléfono
            $table->foreignId('role_id')->constrained('roles')->onDelete('restrict'); // Relación con la tabla de roles
            $table->string('email')->unique(); // Correo electrónico único
            $table->integer('visits_count')->default(0); // Contador de visitas para el usuario
            $table->integer('total_lifetime_visits')->default(0); // Contador de visitas totales a lo largo de la vida del usuario
            $table->longText('avatar')->nullable(); // Avatar del usuario, almacenado como texto largo para permitir URLs o incluso imágenes codificadas en base64
            $table->timestamp('email_verified_at')->nullable(); // Verificación de correo electrónico
            $table->integer('commission')->default(0); // Porcentaje que se lleva el empleado (ej: 50, 40, 60)
            $table->string('password'); // Contraseña
            $table->rememberToken(); // Token para "recordar sesión"
            $table->timestamps(); // Fechas de creación y actualización
            $table->softDeletes(); // Para eliminar usuarios sin borrarlos de la base de datos (opcional, pero recomendado para mantener integridad referencial)
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) { 
            // Esta tabla se utiliza para almacenar los tokens de restablecimiento de contraseña
            $table->string('email')->primary(); // El correo electrónico del usuario, que es la clave primaria
            $table->string('token'); // El token de restablecimiento de contraseña
            $table->timestamp('created_at')->nullable(); // La fecha y hora en que se creó el token, para poder expirar los tokens después de un tiempo determinado
        });

        Schema::create('sessions', function (Blueprint $table) {
            // Esta tabla se utiliza para almacenar las sesiones de los usuarios cuando el controlador de sesiones está configurado para usar la base de datos
            $table->string('id')->primary(); // El ID de la sesión, que es la clave primaria
            $table->foreignId('user_id')->nullable()->index(); // El ID del usuario asociado con la sesión, que puede ser nulo si el usuario no ha iniciado sesión
            $table->string('ip_address', 45)->nullable(); // La dirección IP del usuario que inició la sesión, con un tamaño de 45 caracteres para soportar IPv6
            $table->text('user_agent')->nullable(); // El agente de usuario (navegador, dispositivo, etc.) que se utilizó para iniciar la sesión
            $table->longText('payload'); // El contenido de la sesión, que se almacena como texto largo para permitir almacenar grandes cantidades de datos
            $table->integer('last_activity')->index(); // La fecha y hora de la última actividad en la sesión, almacenada como un entero (timestamp) para facilitar las consultas y la expiración de sesiones inactivas
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
