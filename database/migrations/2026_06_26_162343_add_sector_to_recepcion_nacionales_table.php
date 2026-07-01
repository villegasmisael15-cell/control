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
        // Guardará el sector específico seleccionado (ej. "Sector 1", "Bloque A", etc.)
        $table->string('sector_registro')->after('productor_id')->nullable();
    });
}

public function down(): void
{
    Schema::table('recepcion_nacionales', function (Blueprint $table) {
        $table->dropColumn('sector_registro');
    });
}
};
