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
    Schema::create('monitoreo_clima_riegos', function (Blueprint $table) {
        $table->id();
        $table->date('fecha');
        $table->string('sector');
        $table->decimal('temperatura', 4, 2);
        $table->decimal('humedad', 5, 2);
        $table->decimal('dpv', 4, 2);
        $table->integer('vol_riego_entrada');
        $table->integer('vol_drenaje_salida');
        $table->decimal('porcentaje_drenaje', 5, 2);
        $table->decimal('ce_entrada', 4, 2);
        $table->decimal('ce_salida', 4, 2);
        $table->decimal('diferencia_ce', 4, 2);
        $table->decimal('ph_entrada', 3, 1);
        $table->decimal('ph_salida', 3, 1);
        $table->decimal('diferencia_ph', 3, 1);
        $table->decimal('peso_tarde_anterior', 5, 2);
        $table->decimal('peso_manana', 5, 2);
        $table->decimal('porcentaje_caida_nocturna', 5, 2);
        $table->string('estatus_general')->default('ÓPTIMO');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitoreo_clima_riegos');
    }
};
