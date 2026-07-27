<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('monitoreo_clima_riegos', function (Blueprint $table) {
            $table->decimal('temperatura', 8, 2)->nullable()->change();
            $table->decimal('humedad', 8, 2)->nullable()->change();
            $table->integer('vol_riego_entrada')->nullable()->change();
            $table->integer('vol_drenaje_salida')->nullable()->change();
            $table->decimal('ce_entrada', 8, 2)->nullable()->change();
            $table->decimal('ce_salida', 8, 2)->nullable()->change();
            $table->decimal('ph_entrada', 8, 2)->nullable()->change();
            $table->decimal('ph_salida', 8, 2)->nullable()->change();
            $table->decimal('peso_tarde_anterior', 8, 2)->nullable()->change();
            $table->decimal('peso_manana', 8, 2)->nullable()->change();
            
            // Campos calculados que ahora también pueden recibir nulos
            $table->decimal('dpv', 8, 2)->nullable()->change();
            $table->decimal('porcentaje_drenaje', 8, 2)->nullable()->change();
            $table->decimal('diferencia_ce', 8, 2)->nullable()->change();
            $table->decimal('diferencia_ph', 8, 2)->nullable()->change();
            $table->decimal('porcentaje_caida_nocturna', 8, 2)->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('monitoreo_clima_riegos', function (Blueprint $table) {
            $table->decimal('temperatura', 8, 2)->nullable(false)->change();
            $table->decimal('humedad', 8, 2)->nullable(false)->change();
            $table->integer('vol_riego_entrada')->nullable(false)->change();
            $table->integer('vol_drenaje_salida')->nullable(false)->change();
            $table->decimal('ce_entrada', 8, 2)->nullable(false)->change();
            $table->decimal('ce_salida', 8, 2)->nullable(false)->change();
            $table->decimal('ph_entrada', 8, 2)->nullable(false)->change();
            $table->decimal('ph_salida', 8, 2)->nullable(false)->change();
            $table->decimal('peso_tarde_anterior', 8, 2)->nullable(false)->change();
            $table->decimal('peso_manana', 8, 2)->nullable(false)->change();
            
            $table->decimal('dpv', 8, 2)->nullable(false)->change();
            $table->decimal('porcentaje_drenaje', 8, 2)->nullable(false)->change();
            $table->decimal('diferencia_ce', 8, 2)->nullable(false)->change();
            $table->decimal('diferencia_ph', 8, 2)->nullable(false)->change();
            $table->decimal('porcentaje_caida_nocturna', 8, 2)->nullable(false)->change();
        });
    }
};