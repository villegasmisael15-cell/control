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
        Schema::table('recepcion_nacionales', function (Blueprint $blueprint) {
            // Se crea la llave foránea nullable que conecta con la tabla de exportaciones
            $blueprint->foreignId('recepcion_exportacion_id')
                ->nullable()
                ->after('productor_id') // Se posiciona de manera ordenada después del productor
                ->constrained('recepcion_exportaciones') // Asegúrate de que este sea el nombre exacto de tu tabla de exportación
                ->nullOnDelete(); // Si por alguna razón se borra el embarque, el rechazo no se elimina, queda en null
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('recepcion_nacionales', function (Blueprint $blueprint) {
            // Se elimina la restricción de la llave foránea y la columna
            $blueprint->dropForeign(['recepcion_exportacion_id']);
            $blueprint->dropColumn('recepcion_exportacion_id');
        });
    }
};
