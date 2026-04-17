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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable(); // Nullable por si un producto no necesita tanta explicación
            
            // Costos y precios con decimales
            $table->decimal('cost', 8, 2)->default(0);
            // Decimal con 8 dígitos en total y 2 decimales (ej: 999999.99)
            $table->decimal('price', 8, 2); 
            
            // Medida y unidad (ej: measure = 500, unit = 'ml')
            $table->string('measure')->nullable(); 
            $table->string('unit')->nullable();
            $table->longText('photo')->nullable();
            $table->boolean('is_active')->default(false); // Para marcar si el producto está activo o no

            $table->timestamps(); // Crea created_at y updated_at
            $table->softDeletes(); // Crea deleted_at para soft deletes
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};