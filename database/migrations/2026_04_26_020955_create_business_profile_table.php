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
        Schema::create('business_profile', function (Blueprint $table) {
            $table->id();
            $table->string('name')->default('RS Barber Studio');
            $table->text('description')->nullable();
            $table->string('address')->nullable();
            $table->string('phone')->nullable();
            // Redes sociales y horarios en formato JSON para mayor flexibilidad
            $table->json('social_networks')->nullable(); 
            $table->json('opening_hours')->nullable(); 
            $table->longText('logo')->nullable();
            $table->json('extra_info')->nullable(); // Para cualquier otro dato adicional que queramos guardar sin necesidad de modificar la estructura de la tabla
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('business_profile');
    }
};
