<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mensaje_consulta', function (Blueprint $table) {
            $table->id('id_mensaje_consulta');
            $table->unsignedBigInteger('fk_id_consulta');
            $table->unsignedBigInteger('fk_id_usuario')->nullable();
            $table->enum('emisor', ['familia', 'docente']);
            $table->text('contenido');
            $table->dateTime('fecha')->useCurrent();
            $table->timestamps();

            $table->foreign('fk_id_consulta')
                ->references('id_consulta')
                ->on('consulta')
                ->onDelete('cascade');

            $table->foreign('fk_id_usuario')
                ->references('id_usuario')
                ->on('usuarios')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mensaje_consulta');
    }
};