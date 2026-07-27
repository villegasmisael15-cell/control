<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recepcion_nacionales', function (Blueprint $table) {
            $table->id();
            $table->string('semana_nacional'); // SEMANA #
            $table->date('fecha_nacional'); // FECHA
            
            // PRODUCTOR (Usuario dueño de uno o más sectores)
            $table->foreignId('productor_id')->constrained('users')->onDelete('cascade');
            
            // NACIONAL COMERCIALIZAR
            $table->integer('cajas_comercializar')->default(0); // CAJAS
            $table->decimal('peso_comercializar', 10, 2)->default(0.00); // PESO (KG)
            
            // RECHAZO / NACIONAL PROCESADO 
            // (Representa el total de cajas y peso que se van a procesar)
            $table->integer('cajas_rechazo_procesado')->default(0); // CAJAS
            $table->decimal('peso_rechazo_procesado', 10, 2)->default(0.00); // PESO (KG)
            
            // TOTALES GLOBALES DEL REGISTRO
            $table->integer('total_cajas')->default(0); // TOTAL CAJAS (Comercializar + Rechazo)
            $table->decimal('total_kg', 10, 2)->default(0.00); // TOTAL KG (Comercializar + Rechazo)
            
            $table->foreignId('capturado_por_id')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recepcion_nacionales');
    }
};
