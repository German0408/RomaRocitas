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
        Schema::table('features', function (Blueprint $table) {
            // Solo si existe la columna, intenta eliminarla
            if (Schema::hasColumn('features', 'options_id')) {
                // Elimina la restricción foreign key (Laravel la nombra automáticamente)
                $table->dropForeign(['options_id']);

                // Elimina la columna
                $table->dropColumn('options_id');
            }
        });
    }

    /**
     * Por si haces rollback: vuelve a agregar la columna mal escrita.
     */
    public function down(): void
    {
        Schema::table('features', function (Blueprint $table) {
            $table->foreignId('options_id')
                ->nullable()
                ->constrained('options')
                ->onDelete('cascade');
        });
    }
};
