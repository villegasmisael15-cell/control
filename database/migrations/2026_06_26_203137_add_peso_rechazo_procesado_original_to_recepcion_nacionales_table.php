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
        // Se añade la columna de respaldo justo después del peso de rechazo actual
        $table->decimal('peso_rechazo_procesado_original', 10, 2)->nullable()->after('peso_rechazo_procesado');
    });
}

public function down(): void
{
    Schema::table('recepcion_nacionales', function (Blueprint $table) {
        $table->dropColumn('peso_rechazo_procesado_original');
    });
}
};
