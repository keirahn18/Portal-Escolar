<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CarteleraEventosSeeder extends Seeder
{
    public function run(): void
    {
        // Categorías de Publicación
        DB::table('cat_publicacions')->insert([
            ['nombre' => 'Noticias Institucionales', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Circulares y Comunicados', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Avisos Urgentes', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Categorías de Eventos
        DB::table('cat_eventos')->insert([
            ['nombre' => 'Acto Escolar', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Reunión de Tutores', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Muestra / Feria Pedagógica', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Jornada Institucional', 'created_at' => now(), 'updated_at' => now()],
            ['nombre' => 'Feriado / Asueto', 'created_at' => now(), 'updated_at' => now()],
        ]);

        // Tipos de Eventos (Con colores diferenciados para el calendario)
        DB::table('tipo_eventos')->insert([
            ['tipo' => 'Externo', 'color_hex' => '#0284c7', 'created_at' => now(), 'updated_at' => now()], // Azul Sky (Público + Personal)
            ['tipo' => 'Interno', 'color_hex' => '#d97706', 'created_at' => now(), 'updated_at' => now()], // Ámbar/Naranja (Solo Personal)
        ]);
    }
}