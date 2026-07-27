<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manejo_fertilizantes', function (Blueprint $table) {
            // Añadimos el campo de texto para la acción (ej: "Aplicar en el segundo riego")
            $table->text('accion')->nullable()->after('tanque');
        });
    }

    public function down(): void
    {
        Schema::table('manejo_fertilizantes', function (Blueprint $table) {
            $table->dropColumn('accion');
        });
    }
};