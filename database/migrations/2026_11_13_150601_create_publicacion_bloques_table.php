<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('publicacion_bloques', function (Blueprint $table) {
            $table->id('id_bloque');
            $table->foreignId('fk_id_publicacion')->constrained('publicacions', 'id_publicacion')->onDelete('cascade');
            $table->enum('tipo', ['texto', 'imagen']);
            $table->longText('contenido_texto')->nullable();
            $table->string('ruta_img')->nullable();
            $table->integer('orden')->default(1);
            $table->timestamps();
        });

        // Hacemos nullable 'contenido' en publicacions porque ahora vivirá en los bloques ordenados
        Schema::table('publicacions', function (Blueprint $table) {
            $table->longText('contenido')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('publicacion_bloques');
    }
};