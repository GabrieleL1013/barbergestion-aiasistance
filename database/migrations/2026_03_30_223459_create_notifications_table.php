<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            // Laravel usa UUIDs (texto alfanumérico largo) para las notificaciones en lugar de IDs numéricos
            $table->uuid('id')->primary(); 
            
            // El tipo de notificación (ej: 'RegistroDeVenta', 'PerfilActualizado')
            $table->string('type'); 
            
            // Esto crea mágicamente dos columnas: notifiable_type y notifiable_id (Este es tu DESTINATARIO)
            $table->morphs('notifiable'); 
            
            // Aquí guardas todo lo demás en formato JSON: titulo, descripcion, ejecutor, monto cobrado...
            $table->json('data'); 
            
            // Para saber si el usuario ya vio la notificación o sigue "nueva" en el navbar
            $table->timestamp('read_at')->nullable(); 
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};