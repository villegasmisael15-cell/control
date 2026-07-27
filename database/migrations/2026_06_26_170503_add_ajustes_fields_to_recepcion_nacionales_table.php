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
        // Columnas para almacenar el nuevo dato guardado sin borrar el primero
        $table->integer('cajas_comercializar_ajustado')->nullable()->after('cajas_comercializar');
        $table->decimal('peso_comercializar_ajustado', 10, 2)->nullable()->after('peso_comercializar');
        $table->boolean('fue_ajustado')->default(false)->after('cajas_vacias_totales');
        $table->foreignId('ajustado_por_id')->nullable()->constrained('users')->after('capturado_por_id');
    });
}

public function down(): void
{
    Schema::table('recepcion_nacionales', function (Blueprint $table) {
        $table->dropColumn(['cajas_comercializar_ajustado', 'peso_comercializar_ajustado', 'fue_ajustado', 'ajustado_por_id']);
    });
}
};
