<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recepcion_exportaciones', function (Blueprint $table) {
            $table->id();
            $table->string('semana_exportacion'); // SEMANA #
            $table->date('fecha_exportacion'); // FECHA
            
            // PRODUCTOR (Usuario dueño de uno o más sectores)
            $table->foreignId('productor_id')->constrained('users')->onDelete('cascade');
            
            // EXPORTACIÓN
            $table->integer('cajas_exportacion')->default(0); // CAJAS
            $table->decimal('peso_exportacion', 10, 2)->default(0.00); // PESO
            
            // RESTITUIDAS
            $table->integer('restituidas')->default(0); // RESTITUIDAS
            
            // PENDIENTES (8 DIAS)
            $table->integer('pendientes_8_dias')->default(0);
            
            $table->foreignId('capturado_por_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recepcion_exportaciones');
    }
};