<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id('id_usuario');
            
            // Claves foráneas asociadas a sus PKs personalizadas
            $table->foreignId('fk_id_rol')->constrained('rols', 'id_rol')->onDelete('cascade');
            $table->foreignId('fk_id_institucion')->constrained('institucions', 'id_institucion')->onDelete('cascade');
            
            $table->string('nombre');
            $table->string('apellido');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuarios');
    }
};