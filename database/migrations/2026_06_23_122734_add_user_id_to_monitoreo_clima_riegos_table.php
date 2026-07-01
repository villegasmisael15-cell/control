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
    Schema::table('monitoreo_clima_riegos', function (Blueprint $table) {
        // Se agrega la llave foránea conectada a la tabla users
        $table->foreignId('user_id')->nullable()->after('id')->constrained('users')->onDelete('set null');
    });
}

public function down(): void
{
    Schema::table('monitoreo_clima_riegos', function (Blueprint $table) {
        $table->dropForeign(['user_id']);
        $table->dropColumn('user_id');
    });
}
};
