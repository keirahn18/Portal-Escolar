<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('preguntas_frecuentes', function (Blueprint $table) {
            $table->id('id_preguntas_frecuentes');
            $table->unsignedBigInteger('fk_id_institucion');
            $table->string('pregunta', 255);
            $table->text('respuesta');
            $table->string('palabras_clave', 255)->nullable();
            $table->integer('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamps();

            $table->foreign('fk_id_institucion')
                ->references('id_institucion')
                ->on('institucions')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('preguntas_frecuentes');
    }
};