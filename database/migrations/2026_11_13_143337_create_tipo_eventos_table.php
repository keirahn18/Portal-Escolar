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
        Schema::create('tipo_eventos', function (Blueprint $table) {
            $table->id('id_tipo_evento');
            $table->string('tipo'); // 'Interno' (solo personal) o 'Externo' (público y personal)
            $table->string('color_hex')->default('#0284c7'); // Para diferenciar colores en el calendario
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_eventos');
    }
};
