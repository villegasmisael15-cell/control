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
    Schema::table('recepcion_nacionales', function (Blueprint $table) {
        // Campo aparte para las cajas vacías
        $table->integer('cajas_vacias_totales')->default(0)->after('peso_rechazo_procesado');
    });
}

public function down(): void
{
    Schema::table('recepcion_nacionales', function (Blueprint $table) {
        $table->dropColumn('cajas_vacias_totales');
    });
}
};
