<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('auditoria', function (Blueprint $table) {
            $table->id('id_auditoria');
            $table->unsignedBigInteger('fk_id_usuario');
            $table->string('accion', 100);
            $table->string('entidad', 100);
            $table->unsignedBigInteger('id_registro')->nullable();
            $table->text('detalle')->nullable();
            $table->dateTime('fecha')->useCurrent();
            $table->timestamps();

          $table->foreign('fk_id_usuario')
                ->references('id_usuario')
                ->on('usuarios')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('auditoria');
    }
};