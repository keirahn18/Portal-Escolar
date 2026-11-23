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
        Schema::create('publicacion_imagenes', function (Blueprint $table) {
            $table->id('id_imagen');
            $table->foreignId('fk_id_publicacion')->constrained('publicacions', 'id_publicacion')->onDelete('cascade');
            $table->string('ruta_img');
            $table->integer('orden')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('publicacion_imagenes');
    }
};
