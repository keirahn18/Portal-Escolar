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
        Schema::create('eventos', function (Blueprint $table) {
            $table->id('id_evento');
            $table->foreignId('fk_usuario')->constrained('usuarios', 'id_usuario')->onDelete('cascade');
            $table->foreignId('fk_id_cat_evento')->constrained('cat_eventos', 'id_cat_evento')->onDelete('cascade');
            $table->foreignId('fk_id_tipo_evento')->constrained('tipo_eventos', 'id_tipo_evento')->onDelete('cascade');
            $table->foreignId('fk_id_institucion')->constrained('institucions', 'id_institucion')->onDelete('cascade');
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->date('fecha');
            $table->time('hora_inicio')->nullable();
            $table->time('hora_fin')->nullable();
            $table->string('lugar')->nullable();
            $table->enum('estado', ['activo', 'cancelado', 'finalizado'])->default('activo');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};
