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
        // Cabecera de la Venta
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->nullable()->constrained('users');
            $table->foreignId('barber_id')->constrained('users'); // Quién hizo la venta/servicio
            $table->decimal('total_amount', 8, 2);
            $table->string('payment_method')->default('cash');
            $table->timestamps();
        });

        // Detalle de la Venta (Aquí se diferencia producto de servicio)
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->onDelete('cascade');
            // Usamos polimorfismo o simplemente campos opcionales
            $table->foreignId('product_id')->nullable()->constrained('products');
            $table->foreignId('service_id')->nullable()->constrained('services');
            
            $table->integer('quantity')->default(1);
            $table->decimal('price_at_sale', 8, 2); // Precio al que se vendió en ese momento
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};
