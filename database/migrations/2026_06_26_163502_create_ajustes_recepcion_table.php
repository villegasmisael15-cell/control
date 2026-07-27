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
    Schema::create('ajustes_recepcion', function (Blueprint $table) {
        $table->id();
        $table->foreignId('recepcion_id')->constrained('recepcion_nacionales')->onDelete('cascade');
        $table->integer('cajas_nuevas'); // El dato "agregado"
        $table->decimal('peso_nuevo', 10, 2);
        $table->string('motivo')->nullable(); // Opcional: ¿por qué se ajustó?
        $table->foreignId('ajustado_por')->constrained('users');
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ajustes_recepcion');
    }
};
