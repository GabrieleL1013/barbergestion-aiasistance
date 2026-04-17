<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            
            // El empleado que RECIBE el pago
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            
            // El dueño o administrador que REALIZA el pago
            $table->foreignId('admin_id')->constrained('users')->onDelete('restrict');            
            
            // La cantidad de dinero pagada al empleado
            $table->decimal('amount', 8, 2);
            
            // Cómo se le pagó al empleado (ej: 'Efectivo', 'Transferencia bancaria')
            $table->string('payment_method')->nullable();
            
            // Razón del pago (ej: "Comisiones de la primera quincena de Marzo", "Sueldo base")
            $table->text('notes')->nullable();

            // Foto del comprobante de pago (opcional, se guarda como base64)
            $table->longText('photo')->nullable();

            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('pending');
            
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};