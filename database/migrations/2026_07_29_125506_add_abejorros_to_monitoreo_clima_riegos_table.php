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
    Schema::table('monitoreo_clima_riegos', function (Blueprint $table) {
        $table->integer('abejorros_flores')->nullable();
        $table->string('abejorros_semaforo')->nullable();
    });
}

public function down()
{
    Schema::table('monitoreo_clima_riegos', function (Blueprint $table) {
        $table->dropColumn(['abejorros_flores', 'abejorros_semaforo']);
    });
}
};
