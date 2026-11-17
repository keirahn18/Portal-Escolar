<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consulta', function (Blueprint $table) {
            $table->id('id_consulta');
            $table->unsignedBigInteger('fk_id_institucion');
            $table->unsignedBigInteger('fk_id_usuario')->nullable();
            $table->string('nombre_consultante', 120);
            $table->string('contacto', 150);
            $table->string('asunto', 200);
            $table->enum('estado', ['pendiente', 'en_atencion', 'cerrada'])->default('pendiente');
            $table->string('codigo_seguimiento', 20)->unique();
            $table->dateTime('fecha')->useCurrent();
            $table->timestamps();

            $table->foreign('fk_id_institucion')
                ->references('id_institucion')
                ->on('institucions')
                ->onDelete('cascade');

            $table->foreign('fk_id_usuario')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consulta');
    }
};