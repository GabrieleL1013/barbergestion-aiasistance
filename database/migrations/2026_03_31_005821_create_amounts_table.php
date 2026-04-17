<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('amounts', function (Blueprint $table) {
            $table->id();
            
            // Empleado que realizó el servicio o venta
            $table->foreignId('user_id')->constrained('users')->onDelete('restrict');
            
            // Cliente atendido (Opcional)
            $table->foreignId('client_id')->nullable()->constrained('clients')->onDelete('set null'); // Un ingreso puede no estar asociado a un cliente específico (ej: venta de producto sin servicio)
            
            // Producto vendido (Opcional, si fue solo servicio puede ir nulo)
            $table->foreignId('product_id')->constrained('products')->onDelete('restrict');            
            // Monto ingresado a la barbería
            $table->decimal('amount', 8, 2);
            
            // Método de pago del cliente
            $table->string('payment_method')->nullable();
            
            // Notas (ej: "Corte + Barba")
            $table->text('notes')->nullable();

            // Foto comprobante (opcional, se guarda como base64)
            $table->longText('photo')->nullable();

            $table->foreignId('payment_id')->nullable()->constrained('payments')->onDelete('set null');
            
            $table->timestamps();
            $table->softDeletes(); // Para permitir eliminar ingresos sin perder su historial en caso de errores o ajustes contables
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('amounts');
    }
};