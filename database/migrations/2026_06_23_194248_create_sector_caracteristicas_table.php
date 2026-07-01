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
    Schema::create('sector_caracteristicas', function (Blueprint $table) {
        $table->id();
        $table->string('sector'); // Ej: "Sector 1"
        $table->integer('superficie_m2');
        $table->string('variedad');
        $table->date('fecha_trasplante');
        $table->timestamps();
        
        // Índice para búsquedas rápidas
        $table->unique('sector');
    });
}

public function down(): void
{
    Schema::dropIfExists('sector_caracteristicas');
}
};
