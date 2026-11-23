<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('eventos', function (Blueprint $table) {$table->foreignId('fk_docente_encargado')
                  ->nullable()
                  ->after('fk_id_tipo_evento')
                  ->constrained('usuarios', 'id_usuario')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('eventos', function (Blueprint $table) {
            $table->dropForeign(['fk_docente_encargado']);$table->dropColumn('fk_docente_encargado');
        });
    }
};