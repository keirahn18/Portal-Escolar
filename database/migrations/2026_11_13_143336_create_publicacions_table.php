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
        Schema::create('publicacions', function (Blueprint $table) {
            $table->id('id_publicacion');
            $table->foreignId('fk_usuario')->constrained('usuarios', 'id_usuario')->onDelete('cascade');
            $table->foreignId('fk_id_cat_publicacion')->constrained('cat_publicacions', 'id_cat_publicacion')->onDelete('cascade');
            $table->foreignId('fk_id_institucion')->constrained('institucions', 'id_institucion')->onDelete('cascade');
            $table->string('titulo');
            $table->longText('contenido');
            $table->enum('posicion_texto', ['arriba', 'abajo'])->default('abajo'); // Control de diseño libre
            $table->enum('estado', ['publicado', 'borrador'])->default('publicado');
            $table->timestamp('fecha_publicacion')->useCurrent();
            $table->softDeletes(); // Cumpliendo RNF-25
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publicacions');
    }
};
