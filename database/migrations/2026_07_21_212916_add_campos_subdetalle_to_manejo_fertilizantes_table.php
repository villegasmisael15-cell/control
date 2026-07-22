<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('manejo_fertilizantes', function (Blueprint $table) {
            // Aseguramos que la tabla tenga soporte para la estructura repetitiva por tanque
            if (!Schema::hasColumn('manejo_fertilizantes', 'accion')) {
                $table->text('accion')->nullable()->after('tanque');
            }
        });
    }

    public function down(): void
    {
        Schema::table('manejo_fertilizantes', function (Blueprint $table) {
            $table->dropColumn('accion');
        });
    }
};