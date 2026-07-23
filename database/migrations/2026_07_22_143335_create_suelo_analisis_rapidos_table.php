<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('suelo_analisis_rapidos', function (Blueprint $table) {
        $table->id();
        
        // 💡 SOLUCIÓN DEFINITIVA: Declaramos el campo indexado pura y limpiamente
        // Esto evita que MySQL tire el error de llave foránea ("Foreign key constraint is incorrectly formed")
        $table->unsignedBigInteger('suelo_monitoreo_id')->index();
        
        $table->enum('tipo_analisis', ['eps', 'ecp']);
        $table->string('no3')->nullable();
        $table->string('k')->nullable();
        $table->string('ca')->nullable();
        $table->string('na')->nullable();
        $table->string('p')->nullable();
        $table->string('ph')->nullable();
        $table->string('ce')->nullable();
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('suelo_analisis_rapidos');
    }
};