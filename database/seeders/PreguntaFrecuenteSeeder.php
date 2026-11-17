<?php

namespace Database\Seeders;

use App\Models\Institucion;
use App\Models\PreguntaFrecuente;
use Illuminate\Database\Seeder;

class PreguntaFrecuenteSeeder extends Seeder
{
    public function run(): void
    {
        $institucion = Institucion::first();
        if (!$institucion) {
            return;
        }

        $preguntas = [
            [
                'pregunta'       => '🕒 Horarios de cursado y Secretaría',
                'respuesta'      => "Nuestros horarios habituales son:\n• Turno Mañana: 07:30 a 12:00 hs.\n• Turno Tarde: 13:30 a 18:00 hs.\n• Atención en Secretaría: 08:00 a 11:30 hs y 14:00 a 17:30 hs.",
                'palabras_clave' => 'horario,horarios,turno,mañana,tarde,entrada,salida,secretaria',
                'orden'          => 1,
            ],
            [
                'pregunta'       => '📝 Requisitos de inscripción y pases',
                'respuesta'      => "Para inscripciones o pases necesitás presentar:\n1. Fotocopia de DNI del estudiante y del tutor.\n2. Partida de nacimiento actualizada.\n3. Libreta de salud / calendario de vacunación.\n4. Constancia de pase (si proviene de otra institución).",
                'palabras_clave' => 'inscripcion,inscripciones,requisito,requisitos,vacante,pase,dni,partida',
                'orden'          => 2,
            ],
            [
                'pregunta'       => '📄 Solicitud de constancia de alumno regular',
                'respuesta'      => "Las constancias de alumno regular se solicitan presentando el DNI del estudiante en Secretaría con 24 hs de anticipación. Si necesitás gestionarla por acá, derivá tu consulta indicando nombre completo y grado.",
                'palabras_clave' => 'constancia,certificado,regular,escolaridad,anses',
                'orden'          => 3,
            ],
            [
                'pregunta'       => '📍 Ubicación y contacto institucional',
                'respuesta'      => "Somos la E.P.E.P. N° 436 \"Simón Bolívar\" (Formosa Capital). Podés acercarte en horario escolar o dejarnos una consulta específica desde este asistente para que un docente te responda.",
                'palabras_clave' => 'ubicacion,direccion,donde,queda,telefono,contacto,correo',
                'orden'          => 4,
            ],
        ];

        foreach ($preguntas as $item) {
            PreguntaFrecuente::updateOrCreate(
                [
                    'fk_id_institucion' => $institucion->id_institucion,
                    'pregunta'          => $item['pregunta'],
                ],
                [
                    'respuesta'      => $item['respuesta'],
                    'palabras_clave' => $item['palabras_clave'],
                    'orden'          => $item['orden'],
                    'activo'         => true,
                ]
            );
        }
    }
}