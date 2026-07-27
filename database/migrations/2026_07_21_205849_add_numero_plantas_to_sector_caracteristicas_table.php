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
        Schema::table('sector_caracteristicas', function (Blueprint $table) {
            // Añadimos la columna para el número de plantas (puede ser nulo por si hay registros viejos)
            $table->integer('numero_plantas')->nullable()->after('variedad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sector_caracteristicas', function (Blueprint $table) {
            $table->dropColumn('numero_plantas');
        });
    }
};