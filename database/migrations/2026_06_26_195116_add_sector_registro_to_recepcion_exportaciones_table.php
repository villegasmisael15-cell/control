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
    Schema::table('recepcion_exportaciones', function (Blueprint $table) {
        $table->string('sector_registro')->nullable()->after('productor_id');
    });
}

public function down(): void
{
    Schema::table('recepcion_exportaciones', function (Blueprint $table) {
        $table->dropColumn('sector_registro');
    });
}
};
