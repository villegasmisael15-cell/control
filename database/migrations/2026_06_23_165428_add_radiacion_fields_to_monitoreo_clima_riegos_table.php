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
    Schema::table('monitoreo_clima_riegos', function (Blueprint $table) {
        $table->time('radiacion_hora')->nullable()->after('estatus_general');
        $table->integer('radiacion_lectura')->nullable()->after('radiacion_hora');
        $table->string('radiacion_semaforo')->nullable()->after('radiacion_lectura');
        $table->text('radiacion_accion_tomada')->nullable()->after('radiacion_semaforo');
    });
}

public function down(): void
{
    Schema::table('monitoreo_clima_riegos', function (Blueprint $table) {
        $table->dropColumn(['radiacion_hora', 'radiacion_lectura', 'radiacion_semaforo', 'radiacion_accion_tomada']);
    });
}
};
