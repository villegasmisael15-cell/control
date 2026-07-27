<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
   
   public function up()
{
    Schema::table('suelo_monitoreos', function (Blueprint $table) {
        // Se agrega la columna justo después de 'analisis_rapido_cumplio'
        $table->enum('tipo_analisis_lab', ['fertilidad', 'pasta_saturada'])->nullable()->after('analisis_rapido_cumplio');
    });
}

public function down()
{
    Schema::table('suelo_monitoreos', function (Blueprint $table) {
        $table->dropColumn('tipo_analisis_lab');
    });
}
};
