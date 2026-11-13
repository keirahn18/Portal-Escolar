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
        Schema::create('cat_eventos', function (Blueprint $table) {
            $table->id('id_cat_evento');
            $table->string('nombre'); // Ej: Acto Escolar, Reunión de Tutores, Feria de Ciencias, Feriado
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cat_eventos');
    }
};
